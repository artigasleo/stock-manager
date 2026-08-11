<?php

namespace App\Http\Controllers;

use App\Actions\Stock\CreateStockMovement;
use App\Actions\Stock\ListStockMovement;
use App\Http\Requests\Stock\StoreStockMovementRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request, ListStockMovement $action): View
    {
        return view('stock.index', [
            'movements' => $action->execute($request->integer('product_id') ?: null),
            'products' => Product::orderBy('name')->get(),
            'selectedProductId' => $request->integer('product_id') ?: null,
        ]);
    }

    public function store(
        StoreStockMovementRequest $request,
        CreateStockMovement $action
    ): RedirectResponse {
        $action->execute($request, $request->user());

        return redirect()->route('stock.index')->with('success', 'Movimentação registrada.');
    }
}
