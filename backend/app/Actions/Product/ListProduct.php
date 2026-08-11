<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;

class ListProduct
{
    public function execute(Unit $unit): Collection
    {
        return Product::with([
            'category',
            'supplier',
            'stocks' => fn ($query) => $query->where('unit_id', $unit->id),
        ])->get();
    }
}
