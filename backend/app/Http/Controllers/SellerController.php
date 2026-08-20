<?php

namespace App\Http\Controllers;

use App\Actions\Seller\CreateSeller;
use App\Actions\Seller\DeleteSeller;
use App\Actions\Seller\ListSeller;
use App\Actions\Seller\UpdateSeller;
use App\Http\Requests\Seller\StoreSellerRequest;
use App\Http\Requests\Seller\UpdateSellerRequest;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SellerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sellers.view', only: ['index']),
            new Middleware('permission:sellers.edit', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(ListSeller $action): View
    {
        return view('sellers.index', [
            'sellers' => $action->execute(),
        ]);
    }

    public function store(
        StoreSellerRequest $request,
        CreateSeller $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('sellers.index')->with('success', 'Vendedor criado.');
    }

    public function update(
        UpdateSellerRequest $request,
        Seller $seller,
        UpdateSeller $action
    ): RedirectResponse {
        $action->execute($request, $seller);

        return redirect()->route('sellers.index')->with('success', 'Vendedor atualizado.');
    }

    public function destroy(
        Seller $seller,
        DeleteSeller $action
    ): RedirectResponse {
        $action->execute($seller);

        return redirect()->route('sellers.index')->with('success', 'Vendedor excluído.');
    }
}
