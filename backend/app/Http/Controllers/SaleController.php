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
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(ListSale $action): View
    {
        $unit = Unit::default();

        return view('sales.index', [
            'sales' => $action->execute(),
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::with(['stocks' => fn ($query) => $query->where('unit_id', $unit->id)])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(
        StoreSaleRequest $request,
        CreateSale $action
    ): RedirectResponse {
        $action->execute($request, Unit::default(), $request->user());

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
