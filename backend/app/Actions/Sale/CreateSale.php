<?php

namespace App\Actions\Sale;

use App\Actions\Stock\CreateStockMovement;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSale
{
    public function __construct(
        private CreateStockMovement $createStockMovement
    ) {}

    public function execute(StoreSaleRequest $request, Unit $unit, User $user): Sale
    {
        return DB::transaction(function () use ($request, $unit, $user) {
            $items = $request->validated('items');

            $this->assertStockIsAvailable($unit, $items);

            $sale = Sale::create([
                'unit_id' => $unit->id,
                'customer_id' => $request->validated('customer_id'),
                'seller_id' => $request->validated('seller_id'),
                'user_id' => $user->id,
                'status' => 'awaiting_payment',
                'payment_method' => $request->validated('payment_method'),
                'total' => $this->calculateTotal($items),
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);

                $this->createStockMovement->execute(
                    $unit->id,
                    $item['product_id'],
                    'out',
                    $item['quantity'],
                    "Venda #{$sale->id}",
                    $user,
                );
            }

            return $sale->load('items.product', 'customer');
        });
    }

    private function assertStockIsAvailable(Unit $unit, array $items): void
    {
        foreach ($items as $index => $item) {
            $product = Product::find($item['product_id']);
            $available = ProductStock::where('unit_id', $unit->id)
                ->where('product_id', $item['product_id'])
                ->first()?->quantity ?? 0;

            if ($product && $item['quantity'] > $available) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => "Estoque insuficiente para {$product->name}. Disponível: {$available}.",
                ]);
            }
        }
    }

    private function calculateTotal(array $items): float
    {
        return array_sum(array_map(
            fn (array $item) => $item['quantity'] * $item['unit_price'],
            $items
        ));
    }
}
