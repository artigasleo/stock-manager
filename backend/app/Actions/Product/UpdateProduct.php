<?php

namespace App\Actions\Product;

use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Unit;

class UpdateProduct
{
    public function execute(UpdateProductRequest $request, Product $product, Unit $unit): Product
    {
        $product->fill([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'barcode' => $request->validated('barcode'),
            'category_id' => $request->validated('category_id'),
            'supplier_id' => $request->validated('supplier_id'),
            'expiration_date' => $request->validated('expiration_date'),
            'cost_price' => $request->validated('cost_price'),
            'sale_price' => $request->validated('sale_price'),
            'active' => $request->validated('active') ?? $product->active,
            'ncm' => $request->validated('ncm'),
            'cest' => $request->validated('cest'),
            'cfop' => $request->validated('cfop'),
            'csosn' => $request->validated('csosn'),
            'unidade_comercial' => $request->validated('unidade_comercial') ?? 'UN',
            'origem' => $request->validated('origem') ?? 0,
        ]);

        $product->save();

        ProductStock::updateOrCreate(
            ['unit_id' => $unit->id, 'product_id' => $product->id],
            ['min_stock' => $request->validated('min_stock') ?? 0],
        );

        return $product->load(['category', 'supplier', 'stocks']);
    }
}
