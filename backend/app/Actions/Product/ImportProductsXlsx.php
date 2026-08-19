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

            if ($name === '' || $code === '' || $categoryName === '' || $costPrice === null || $salePrice === null) {
                $warnings[] = "Linha {$rowNumber}: nome, código, categoria, preço de custo e preço de venda são obrigatórios.";

                continue;
            }

            $category = Category::firstOrCreate(['name' => $categoryName], ['active' => true]);
            $supplier = $supplierName !== ''
                ? Supplier::firstOrCreate(['name' => $supplierName], ['active' => true])
                : null;

            $product = Product::where('code', $code)->first();

            $wasCreated = DB::transaction(function () use (
                $product, $name, $code, $barcode, $category, $supplier,
                $costPrice, $salePrice, $expirationDate, $minStock,
                $initialQuantity, $unit, $user
            ) {
                if ($product) {
                    $product->fill([
                        'name' => $name,
                        'barcode' => $barcode,
                        'category_id' => $category->id,
                        'supplier_id' => $supplier?->id,
                        'expiration_date' => $expirationDate,
                        'cost_price' => $costPrice,
                        'sale_price' => $salePrice,
                    ]);
                    $product->save();

                    ProductStock::updateOrCreate(
                        ['unit_id' => $unit->id, 'product_id' => $product->id],
                        ['min_stock' => $minStock],
                    );

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
                    'active' => true,
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
