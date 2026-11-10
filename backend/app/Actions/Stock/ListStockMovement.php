<?php

namespace App\Actions\Stock;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ListStockMovement
{
    public function execute(?int $productId = null, int|string $perPage = 25): Collection|LengthAwarePaginator
    {
        $query = StockMovement::query()
            ->with(['product', 'user'])
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->latest();

        if ($perPage === 'all') {
            return $query->get();
        }

        return $query->paginate((int) $perPage)->withQueryString();
    }
}
