<?php

namespace App\Actions\Purchase;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;

class ListPurchase
{
    public function execute(): Collection
    {
        return Purchase::query()
            ->with(['supplier', 'items.product', 'user'])
            ->latest()
            ->get();
    }
}
