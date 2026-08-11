<?php

namespace App\Actions\Stock;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;

class ListStockMovement
{
    public function execute(?int $productId = null): Collection
    {
        return StockMovement::query()
            ->with(['product', 'user'])
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->latest()
            ->get();
    }
}
