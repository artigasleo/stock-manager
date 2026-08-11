<?php

namespace App\Actions\Stock;

use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateStockMovement
{
    public function execute(
        int $unitId,
        int $productId,
        string $type,
        int $quantity,
        ?string $reason,
        User $user
    ): StockMovement {
        return DB::transaction(function () use ($unitId, $productId, $type, $quantity, $reason, $user) {
            $stock = ProductStock::query()
                ->where('unit_id', $unitId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = ProductStock::create([
                    'unit_id' => $unitId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'min_stock' => 0,
                ]);
            }

            if ($type === 'out' && $quantity > $stock->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Estoque insuficiente. Disponível: {$stock->quantity}.",
                ]);
            }

            $stock->quantity += $type === 'in' ? $quantity : -$quantity;
            $stock->save();

            return StockMovement::create([
                'unit_id' => $unitId,
                'product_id' => $productId,
                'user_id' => $user->id,
                'type' => $type,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);
        });
    }
}
