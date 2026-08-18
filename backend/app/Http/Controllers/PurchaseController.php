<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreatePurchase;
use App\Actions\Purchase\ListPurchase;
use App\Actions\Purchase\ParseNfeXml;
use App\Http\Requests\Purchase\ImportNfeRequest;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchases.view', only: ['index']),
            new Middleware('permission:purchases.edit', only: ['store', 'import']),
        ];
    }

    public function index(ListPurchase $action): View
    {
        return view('purchases.index', [
            'purchases' => $action->execute(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'units' => Unit::where('active', true)->orderBy('name')->get(),
            'defaultUnit' => Unit::default(),
        ]);
    }

    public function store(
        StorePurchaseRequest $request,
        CreatePurchase $action
    ): RedirectResponse {
        $unit = Unit::findOrFail($request->validated('unit_id'));

        $action->execute($request, $unit, $request->user());

        return redirect()->route('purchases.index')->with('success', 'Compra registrada.');
    }

    public function import(
        ImportNfeRequest $request,
        ParseNfeXml $parser
    ): RedirectResponse {
        try {
            $parsed = $parser->execute($request->file('xml_file')->get());
        } catch (InvalidArgumentException $e) {
            return redirect()->route('purchases.index')->with('error', $e->getMessage());
        }

        $supplier = Supplier::where('document', $parsed['supplier_document'])->first();
        $unmatchedCodes = [];

        $items = array_map(function (array $item) use (&$unmatchedCodes) {
            $product = $item['barcode']
                ? Product::where('barcode', $item['barcode'])->first()
                : null;

            $product ??= Product::where('code', $item['code'])->first();

            if (! $product) {
                $unmatchedCodes[] = $item['code'];
            }

            return [
                'product_id' => $product?->id ?? '',
                'quantity' => (string) $item['quantity'],
                'unit_cost' => (string) $item['unit_cost'],
            ];
        }, $parsed['items']);

        $warning = null;

        if (! $supplier) {
            $warning = "Fornecedor com CNPJ {$parsed['supplier_document']} ({$parsed['supplier_name']}) não está cadastrado. Cadastre-o em Fornecedores e selecione manualmente.";
        }

        if (! empty($unmatchedCodes)) {
            $warning = trim(($warning ? $warning.' ' : '').'Produtos não encontrados pelo código '.implode(', ', $unmatchedCodes).'. Selecione manualmente.');
        }

        $redirect = redirect()->route('purchases.index')->withInput([
            'supplier_id' => $supplier?->id,
            'invoice_number' => $parsed['invoice_number'],
            'items' => $items,
        ]);

        return $warning ? $redirect->with('warning', $warning) : $redirect;
    }
}
