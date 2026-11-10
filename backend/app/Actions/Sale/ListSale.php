<?php

namespace App\Actions\Sale;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ListSale
{
    public function execute(int|string $perPage = 25, int $page = 1): Collection|LengthAwarePaginator
    {
        $query = Sale::query()
            ->with(['customer', 'seller', 'user', 'items.product', 'nfce', 'payments'])
            ->latest();

        if ($perPage === 'all') {
            return $query->get();
        }

        return $query->paginate((int) $perPage, ['*'], 'page', $page)->withQueryString();
    }
}
