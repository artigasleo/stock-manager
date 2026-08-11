<?php

namespace App\Actions\Sale;

use App\Actions\Stock\CreateStockMovement;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSale
{
    public function __construct(
        private CreateStockMovement $createStockMovement
    ) {}

    public function execute(StoreSaleRequest $request, User $user): Sale
    {
        return DB::transaction(function () use ($request, $user) {
            $items = $request->validated('items');

            $this->assertStockIsAvailable($items);

            $sale = Sale::create([
                'customer_id' => $request->validated('customer_id'),
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

    private function assertStockIsAvailable(array $items): void
    {
        foreach ($items as $index => $item) {
            $product = Product::find($item['product_id']);

            if ($product && $item['quantity'] > $product->quantity) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => "Estoque insuficiente para {$product->name}. Disponível: {$product->quantity}.",
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
