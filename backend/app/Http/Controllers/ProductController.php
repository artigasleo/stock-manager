<?php

namespace App\Http\Controllers;

use App\Actions\Product\CreateProduct;
use App\Actions\Product\DeleteProduct;
use App\Actions\Product\ListProduct;
use App\Actions\Product\UpdateProduct;
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

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view', only: ['index']),
            new Middleware('permission:products.edit', only: ['store', 'update', 'destroy']),
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
}
