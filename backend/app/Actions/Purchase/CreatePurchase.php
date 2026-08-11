<?php

namespace App\Actions\Purchase;

use App\Actions\Stock\CreateStockMovement;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePurchase
{
    public function __construct(
        private CreateStockMovement $createStockMovement
    ) {}

    public function execute(StorePurchaseRequest $request, User $user): Purchase
    {
        return DB::transaction(function () use ($request, $user) {
            $items = $request->validated('items');

            $purchase = Purchase::create([
                'supplier_id' => $request->validated('supplier_id'),
                'user_id' => $user->id,
                'invoice_number' => $request->validated('invoice_number'),
                'total' => $this->calculateTotal($items),
            ]);

            foreach ($items as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);

                $this->createStockMovement->execute(
                    $item['product_id'],
                    'in',
                    $item['quantity'],
                    "Compra #{$purchase->id}",
                    $user,
                );

                Product::whereKey($item['product_id'])->update([
                    'cost_price' => $item['unit_cost'],
                ]);
            }

            return $purchase->load('items.product', 'supplier');
        });
    }

    private function calculateTotal(array $items): float
    {
        return array_sum(array_map(
            fn (array $item) => $item['quantity'] * $item['unit_cost'],
            $items
        ));
    }
}
