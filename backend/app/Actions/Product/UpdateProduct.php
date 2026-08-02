<?php

namespace App\Actions\Product;

use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;

class UpdateProduct
{
    public function execute(UpdateProductRequest $request, Product $product): Product
    {
        $product->fill([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'category_id' => $request->validated('category_id'),
            'supplier_id' => $request->validated('supplier_id'),
            'quantity' => $request->validated('quantity') ?? $product->quantity,
            'min_stock' => $request->validated('min_stock') ?? $product->min_stock,
            'expiration_date' => $request->validated('expiration_date'),
            'cost_price' => $request->validated('cost_price'),
            'sale_price' => $request->validated('sale_price'),
            'active' => $request->validated('active') ?? $product->active,
        ]);

        $product->save();

        return $product->load(['category', 'supplier']);
    }
}
