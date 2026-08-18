<?php

namespace App\Http\Controllers;

use App\Actions\Supplier\CreateSupplier;
use App\Actions\Supplier\DeleteSupplier;
use App\Actions\Supplier\ListSupplier;
use App\Actions\Supplier\UpdateSupplier;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SupplierController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:suppliers.view', only: ['index']),
            new Middleware('permission:suppliers.edit', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(ListSupplier $action): View
    {
        return view('suppliers.index', [
            'suppliers' => $action->execute(),
        ]);
    }

    public function store(
        StoreSupplierRequest $request,
        CreateSupplier $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('suppliers.index')->with('success', 'Fornecedor criado.');
    }

    public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier,
        UpdateSupplier $action
    ): RedirectResponse {
        $action->execute($request, $supplier);

        return redirect()->route('suppliers.index')->with('success', 'Fornecedor atualizado.');
    }

    public function destroy(
        Supplier $supplier,
        DeleteSupplier $action
    ): RedirectResponse {
        $action->execute($supplier);

        return redirect()->route('suppliers.index')->with('success', 'Fornecedor excluído.');
    }
}
