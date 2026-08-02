<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ListProduct
{
    public function execute(): Collection
    {
        return Product::with(['category', 'supplier'])->get();
    }
}
