<?php

namespace App\Http\Controllers;

use App\Actions\Sale\CreateSale;
use App\Actions\Sale\ListSale;
use App\Actions\Sale\UpdateSaleStatus;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Requests\Sale\UpdateSaleStatusRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SaleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sales.view', only: ['index']),
            new Middleware('permission:sales.edit', only: ['store', 'updateStatus']),
        ];
    }

    public function index(ListSale $action): View
    {
        return view('sales.index', [
            'sales' => $action->execute(),
            'customers' => Customer::orderBy('name')->get(),
            'sellers' => Seller::where('active', true)->orderBy('name')->get(),
            'products' => Product::with('stocks')->orderBy('name')->get(),
            'units' => Unit::where('active', true)->orderBy('name')->get(),
            'defaultUnit' => Unit::default(),
        ]);
    }

    public function store(
        StoreSaleRequest $request,
        CreateSale $action
    ): RedirectResponse {
        $unit = Unit::findOrFail($request->validated('unit_id'));

        $action->execute($request, $unit, $request->user());

        return redirect()->route('sales.index')->with('success', 'Venda registrada.');
    }

    public function updateStatus(
        UpdateSaleStatusRequest $request,
        Sale $sale,
        UpdateSaleStatus $action
    ): RedirectResponse {
        $action->execute($request, $sale, $request->user());

        return redirect()->route('sales.index')->with('success', 'Status da venda atualizado.');
    }
}
