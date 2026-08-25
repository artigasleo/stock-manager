<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;

class ListProduct
{
    private const SORTABLE = [
        'code' => 'code',
        'barcode' => 'barcode',
        'name' => 'name',
        'category' => 'category.name',
        'supplier' => 'supplier.name',
        'quantity' => 'quantity',
        'sale_price' => 'sale_price',
        'status' => 'active',
    ];

    public function execute(Unit $unit, ?string $sort = null, string $direction = 'asc'): Collection
    {
        $products = Product::with([
            'category',
            'supplier',
            'stocks' => fn ($query) => $query->where('unit_id', $unit->id),
        ])->get();

        if (! isset(self::SORTABLE[$sort])) {
            return $products;
        }

        $key = self::SORTABLE[$sort];
        $callback = $key === 'quantity'
            ? fn (Product $product) => $product->stocks->first()?->quantity ?? 0
            : fn (Product $product) => data_get($product, $key);

        return $direction === 'desc'
            ? $products->sortByDesc($callback)->values()
            : $products->sortBy($callback)->values();
    }
}
