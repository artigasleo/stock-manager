<?php

namespace App\Actions\Product;

use App\Actions\Stock\CreateStockMovement;
use App\Http\Requests\Product\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    public function __construct(
        private CreateStockMovement $createStockMovement
    ) {}

    public function execute(StoreProductRequest $request, Unit $unit, User $user): Product
    {
        return DB::transaction(function () use ($request, $unit, $user) {
            $product = Product::create([
                'name' => $request->validated('name'),
                'code' => $request->validated('code'),
                'barcode' => $request->validated('barcode'),
                'category_id' => $request->validated('category_id'),
                'supplier_id' => $request->validated('supplier_id'),
                'expiration_date' => $request->validated('expiration_date'),
                'cost_price' => $request->validated('cost_price'),
                'sale_price' => $request->validated('sale_price'),
                'active' => $request->validated('active') ?? true,
            ]);

            ProductStock::create([
                'unit_id' => $unit->id,
                'product_id' => $product->id,
                'quantity' => 0,
                'min_stock' => $request->validated('min_stock') ?? 0,
            ]);

            $initialQuantity = $request->validated('quantity');

            if ($initialQuantity > 0) {
                $this->createStockMovement->execute(
                    $unit->id,
                    $product->id,
                    'in',
                    $initialQuantity,
                    'Estoque inicial',
                    $user,
                );
            }

            return $product->load(['category', 'supplier', 'stocks']);
        });
    }
}
