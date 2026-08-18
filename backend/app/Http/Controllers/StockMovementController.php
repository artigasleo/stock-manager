<?php

namespace App\Http\Controllers;

use App\Actions\Stock\CreateStockMovement;
use App\Actions\Stock\ListStockMovement;
use App\Http\Requests\Stock\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request, ListStockMovement $action): View
    {
        return view('movimentacoes.index', [
            'movements' => $action->execute($request->integer('product_id') ?: null),
            'products' => Product::with('stocks')->orderBy('name')->get(),
            'units' => Unit::where('active', true)->orderBy('name')->get(),
            'defaultUnit' => Unit::default(),
            'selectedProductId' => $request->integer('product_id') ?: null,
        ]);
    }

    public function store(
        StoreStockMovementRequest $request,
        CreateStockMovement $action
    ): RedirectResponse {
        $action->execute(
            $request->validated('unit_id'),
            $request->validated('product_id'),
            $request->validated('type'),
            $request->validated('quantity'),
            $request->validated('reason'),
            $request->user(),
        );

        return redirect()->route('stock.index')->with('success', 'Movimentação registrada.');
    }
}
