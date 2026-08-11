<?php

namespace App\Actions\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateStockMovement
{
    public function execute(
        int $productId,
        string $type,
        int $quantity,
        ?string $reason,
        User $user
    ): StockMovement {
        return DB::transaction(function () use ($productId, $type, $quantity, $reason, $user) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            if ($type === 'out' && $quantity > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Estoque insuficiente. Disponível: {$product->quantity}.",
                ]);
            }

            $product->quantity += $type === 'in' ? $quantity : -$quantity;
            $product->save();

            return StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'type' => $type,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);
        });
    }
}
