<?php

namespace App\Http\Controllers;

use App\Actions\Product\CreateProduct;
use App\Actions\Product\DeleteProduct;
use App\Actions\Product\ImportProductsXlsx;
use App\Actions\Product\ListProduct;
use App\Actions\Product\UpdateProduct;
use App\Http\Requests\Product\ImportProductsXlsxRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller implements HasMiddleware
{
    private const IMPORT_HEADER = ['Nome', 'Código', 'Código de Barras', 'Categoria', 'Fornecedor', 'Preço de Custo', 'Preço de Venda', 'Estoque Inicial', 'Estoque Mínimo', 'Validade'];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view', only: ['index']),
            new Middleware('permission:products.edit', only: ['store', 'update', 'destroy', 'import', 'template', 'export']),
        ];
    }

    public function index(ListProduct $action): View
    {
        return view('estoque.index', [
            'products' => $action->execute(Unit::default()),
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(
        StoreProductRequest $request,
        CreateProduct $action
    ): RedirectResponse {
        $action->execute($request, Unit::default(), $request->user());

        return redirect()->route('products.index')->with('success', 'Produto criado.');
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProduct $action
    ): RedirectResponse {
        $action->execute($request, $product, Unit::default());

        return redirect()->route('products.index')->with('success', 'Produto atualizado.');
    }

    public function destroy(
        Product $product,
        DeleteProduct $action
    ): RedirectResponse {
        $action->execute($product);

        return redirect()->route('products.index')->with('success', 'Produto excluído.');
    }

    public function import(
        ImportProductsXlsxRequest $request,
        ImportProductsXlsx $action
    ): RedirectResponse {
        $result = $action->execute(
            $request->file('xlsx_file')->getRealPath(),
            Unit::default(),
            $request->user(),
        );

        $success = "Importação concluída: {$result['created']} produto(s) criado(s), {$result['updated']} atualizado(s).";
        $redirect = redirect()->route('products.index')->with('success', $success);

        if (! empty($result['warnings'])) {
            $redirect->with('warning', implode(' ', $result['warnings']));
        }

        return $redirect;
    }

    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            self::IMPORT_HEADER,
            ['Chocolate ao Leite 90g', 'CHO001', '7891000001234', 'Chocolates', 'Distribuidora Central', 5.50, 8.99, 40, 5, ''],
        ]);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'modelo-importacao-produtos.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function export(): StreamedResponse
    {
        $unit = Unit::default();

        $products = Product::with([
            'category',
            'supplier',
            'stocks' => fn ($query) => $query->where('unit_id', $unit->id),
        ])->orderBy('name')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([self::IMPORT_HEADER]);

        foreach ($products as $index => $product) {
            $stock = $product->stocks->first();

            $sheet->fromArray([[
                $product->name,
                $product->code,
                $product->barcode,
                $product->category->name,
                $product->supplier?->name,
                (float) $product->cost_price,
                (float) $product->sale_price,
                $stock?->quantity ?? 0,
                $stock?->min_stock ?? 0,
                $product->expiration_date?->format('Y-m-d'),
            ]], null, 'A'.($index + 2));
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'produtos-cadastrados-'.now()->format('Y-m-d').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
