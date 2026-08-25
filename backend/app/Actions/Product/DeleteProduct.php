<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class DeleteProduct
{
    public function execute(Product $product): void
    {
        if ($product->saleItems()->exists()) {
            throw ValidationException::withMessages([
                'product' => 'Este produto já possui vendas registradas e não pode ser excluído.',
            ]);
        }

        $product->stockMovements()->delete();
        $product->purchaseItems()->delete();
        $product->forceDelete();
    }
}
