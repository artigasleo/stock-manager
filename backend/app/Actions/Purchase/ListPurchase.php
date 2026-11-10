<?php

namespace App\Actions\Purchase;

use App\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ListPurchase
{
    public function execute(int|string $perPage = 25): Collection|LengthAwarePaginator
    {
        $query = Purchase::query()
            ->with(['supplier', 'items.product', 'user'])
            ->latest();

        if ($perPage === 'all') {
            return $query->get();
        }

        return $query->paginate((int) $perPage)->withQueryString();
    }
}
