<?php

namespace App\Actions\Stock;

use App\Http\Requests\Stock\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateStockMovement
{
    public function execute(StoreStockMovementRequest $request, User $user): StockMovement
    {
        return DB::transaction(function () use ($request, $user) {
            $product = Product::lockForUpdate()->findOrFail($request->validated('product_id'));

            $type = $request->validated('type');
            $quantity = $request->validated('quantity');

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
                'reason' => $request->validated('reason'),
            ]);
        });
    }
}
