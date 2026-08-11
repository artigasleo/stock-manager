<?php

namespace App\Actions\Sale;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;

class ListSale
{
    public function execute(): Collection
    {
        return Sale::query()
            ->with(['customer', 'user', 'items.product'])
            ->latest()
            ->get();
    }
}
