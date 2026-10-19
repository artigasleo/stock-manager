<?php

namespace App\Actions\Product;

use App\Actions\Stock\CreateStockMovement;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportProductsXlsx
{
    public function __construct(
        private CreateStockMovement $createStockMovement
    ) {}

    /**
     * @return array{created: int, updated: int, warnings: array<int, string>}
     */
    public function execute(string $filePath, Unit $unit, User $user): array
    {
        $rows = IOFactory::load($filePath)
            ->getActiveSheet()
            ->toArray(null, true, false, false);

        $created = 0;
        $updated = 0;
        $warnings = [];
        $seenCodes = [];
        $seenBarcodes = [];

        foreach (array_slice($rows, 1, null, true) as $index => $row) {
            $rowNumber = $index + 1;

            // Formatted-but-empty rows at the end of a sheet come back as all nulls.
            if (collect($row)->every(fn ($cell) => trim((string) $cell) === '')) {
                continue;
            }

            $name = trim((string) ($row[0] ?? ''));
            $code = trim((string) ($row[1] ?? ''));
            $barcode = trim((string) ($row[2] ?? '')) ?: null;
            $categoryName = trim((string) ($row[3] ?? ''));
            $supplierName = trim((string) ($row[4] ?? ''));
            $costPrice = $this->parseNumber($row[5] ?? null);
            $salePrice = $this->parseNumber($row[6] ?? null);
            $initialQuantity = (int) ($this->parseNumber($row[7] ?? null) ?? 0);
            $minStock = (int) ($this->parseNumber($row[8] ?? null) ?? 0);
            $expirationDate = $this->parseDate($row[9] ?? null);
            $statusRaw = trim((string) ($row[10] ?? ''));
            $ncmRaw = trim((string) ($row[11] ?? ''));

            if ($name === '' || $code === '' || $categoryName === '' || $costPrice === null || $salePrice === null) {
                $warnings[] = "Linha {$rowNumber}: nome, código, categoria, preço de custo e preço de venda são obrigatórios.";

                continue;
            }

            if (mb_strlen($name) > 150 || mb_strlen($code) > 50 || mb_strlen((string) $barcode) > 64
                || mb_strlen($categoryName) > 100 || mb_strlen($supplierName) > 150) {
                $warnings[] = "Linha {$rowNumber}: texto longo demais (nome até 150, código até 50, código de barras até 64, categoria até 100, fornecedor até 150 caracteres).";

                continue;
            }

            if ($costPrice < 0 || $salePrice < 0 || $costPrice > 99999999.99 || $salePrice > 99999999.99) {
                $warnings[] = "Linha {$rowNumber}: preço inválido.";

                continue;
            }

            $active = true;

            if ($statusRaw !== '') {
                $normalizedStatus = mb_strtolower($statusRaw);

                if (! in_array($normalizedStatus, ['ativo', 'inativo'], true)) {
                    $warnings[] = "Linha {$rowNumber}: status \"{$statusRaw}\" inválido (use Ativo ou Inativo).";

                    continue;
                }

                $active = $normalizedStatus === 'ativo';
            }

            $ncm = null;

            if ($ncmRaw !== '') {
                if (! ctype_digit($ncmRaw) || strlen($ncmRaw) !== 8) {
                    $warnings[] = "Linha {$rowNumber}: NCM \"{$ncmRaw}\" inválido (precisa ter exatamente 8 dígitos numéricos).";

                    continue;
                }

                $ncm = $ncmRaw;
            }

            if (isset($seenCodes[$code])) {
                $warnings[] = "Linha {$rowNumber}: código \"{$code}\" duplicado na planilha (já usado na linha {$seenCodes[$code]}).";

                continue;
            }

            $seenCodes[$code] = $rowNumber;

            if ($barcode !== null) {
                if (isset($seenBarcodes[$barcode])) {
                    $warnings[] = "Linha {$rowNumber}: código de barras \"{$barcode}\" duplicado na planilha (já usado na linha {$seenBarcodes[$barcode]}).";

                    continue;
                }

                $seenBarcodes[$barcode] = $rowNumber;

                $barcodeOwner = Product::withTrashed()
                    ->where('barcode', $barcode)
                    ->where('code', '!=', $code)
                    ->value('code');

                if ($barcodeOwner !== null) {
                    $warnings[] = "Linha {$rowNumber}: código de barras \"{$barcode}\" já pertence ao produto \"{$barcodeOwner}\".";

                    continue;
                }
            }

            try {
                $wasCreated = $this->importRow(
                    $code, $name, $barcode, $categoryName, $supplierName,
                    $costPrice, $salePrice, $expirationDate, $minStock,
                    $initialQuantity, $active, $ncm, $unit, $user, $rowNumber, $warnings
                );
            } catch (QueryException $e) {
                Log::error("Importação XLSX: falha na linha {$rowNumber}", ['exception' => $e]);
                $warnings[] = "Linha {$rowNumber}: não foi possível salvar o produto (verifique os dados da linha).";

                continue;
            }

            $wasCreated ? $created++ : $updated++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  array<int, string>  $warnings
     */
    private function importRow(
        string $code, string $name, ?string $barcode, string $categoryName, string $supplierName,
        float $costPrice, float $salePrice, ?string $expirationDate, int $minStock,
        int $initialQuantity, bool $active, ?string $ncm, Unit $unit, User $user, int $rowNumber, array &$warnings
    ): bool {
        return DB::transaction(function () use (
            $code, $name, $barcode, $categoryName, $supplierName,
            $costPrice, $salePrice, $expirationDate, $minStock,
            $initialQuantity, $active, $ncm, $unit, $user, $rowNumber, &$warnings
        ) {
            $category = Category::firstOrCreate(['name' => $categoryName], ['active' => true]);
            $supplier = $supplierName !== ''
                ? Supplier::firstOrCreate(['name' => $supplierName], ['active' => true])
                : null;

            $product = Product::withTrashed()->where('code', $code)->first();

            if ($product) {
                if ($product->trashed()) {
                    $product->restore();
                }

                $product->fill([
                    'name' => $name,
                    'barcode' => $barcode,
                    'category_id' => $category->id,
                    'supplier_id' => $supplier?->id,
                    'expiration_date' => $expirationDate,
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'active' => $active,
                    ...($ncm !== null ? ['ncm' => $ncm] : []),
                ]);
                $product->save();

                $stock = ProductStock::firstOrNew(['unit_id' => $unit->id, 'product_id' => $product->id]);
                $currentQuantity = $stock->exists ? $stock->quantity : 0;
                $stock->min_stock = $minStock;
                $stock->save();

                if ($initialQuantity !== $currentQuantity) {
                    $warnings[] = "Linha {$rowNumber}: estoque inicial não foi atualizado (produto já existe, ficou em {$currentQuantity}). Para alterar a quantidade, use Movimentações.";
                }

                return false;
            }

            $newProduct = Product::create([
                'name' => $name,
                'code' => $code,
                'barcode' => $barcode,
                'category_id' => $category->id,
                'supplier_id' => $supplier?->id,
                'expiration_date' => $expirationDate,
                'cost_price' => $costPrice,
                'sale_price' => $salePrice,
                'active' => $active,
                'ncm' => $ncm,
            ]);

            ProductStock::create([
                'unit_id' => $unit->id,
                'product_id' => $newProduct->id,
                'quantity' => 0,
                'min_stock' => $minStock,
            ]);

            if ($initialQuantity > 0) {
                $this->createStockMovement->execute(
                    $unit->id,
                    $newProduct->id,
                    'in',
                    $initialQuantity,
                    'Estoque inicial (importação)',
                    $user,
                );
            }

            return true;
        });
    }

    private function parseNumber(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = str_replace(',', '.', trim($value));

            if (is_numeric($normalized)) {
                return (float) $normalized;
            }
        }

        return null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (Exception) {
            return null;
        }
    }
}
