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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        foreach (array_slice($rows, 1, null, true) as $index => $row) {
            $rowNumber = $index + 1;

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

            if ($name === '' || $code === '' || $categoryName === '' || $costPrice === null || $salePrice === null) {
                $warnings[] = "Linha {$rowNumber}: nome, código, categoria, preço de custo e preço de venda são obrigatórios.";

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

            if (isset($seenCodes[$code])) {
                $warnings[] = "Linha {$rowNumber}: código \"{$code}\" duplicado na planilha (já usado na linha {$seenCodes[$code]}).";

                continue;
            }

            $seenCodes[$code] = $rowNumber;

            $category = Category::firstOrCreate(['name' => $categoryName], ['active' => true]);
            $supplier = $supplierName !== ''
                ? Supplier::firstOrCreate(['name' => $supplierName], ['active' => true])
                : null;

            $product = Product::withTrashed()->where('code', $code)->first();

            $wasCreated = DB::transaction(function () use (
                $product, $name, $code, $barcode, $category, $supplier,
                $costPrice, $salePrice, $expirationDate, $minStock,
                $initialQuantity, $active, $unit, $user, $rowNumber, &$warnings
            ) {
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

            $wasCreated ? $created++ : $updated++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'warnings' => $warnings,
        ];
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
