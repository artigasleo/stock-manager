<?php

namespace App\Actions\Product;

use App\Http\Requests\Product\StoreProductRequest;
use App\Models\Product;

class CreateProduct
{
    public function execute(StoreProductRequest $request): Product
    {
        $product = Product::create([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'category_id' => $request->validated('category_id'),
            'supplier_id' => $request->validated('supplier_id'),
            'quantity' => $request->validated('quantity') ?? 0,
            'min_stock' => $request->validated('min_stock') ?? 0,
            'expiration_date' => $request->validated('expiration_date'),
            'cost_price' => $request->validated('cost_price'),
            'sale_price' => $request->validated('sale_price'),
            'active' => $request->validated('active') ?? true,
        ]);

        return $product->load(['category', 'supplier']);
    }
}
