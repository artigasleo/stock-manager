<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreatePurchase;
use App\Actions\Purchase\ListPurchase;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(ListPurchase $action): View
    {
        return view('purchases.index', [
            'purchases' => $action->execute(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(
        StorePurchaseRequest $request,
        CreatePurchase $action
    ): RedirectResponse {
        $action->execute($request, $request->user());

        return redirect()->route('purchases.index')->with('success', 'Compra registrada.');
    }
}
