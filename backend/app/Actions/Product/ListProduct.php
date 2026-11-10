<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function execute(
        Unit $unit,
        ?string $sort = null,
        string $direction = 'asc',
        ?string $search = null,
        int|string $perPage = 25,
        int $page = 1,
    ): Collection|LengthAwarePaginatorContract {
        $products = Product::with([
            'category',
            'supplier',
            'stocks' => fn ($query) => $query->where('unit_id', $unit->id),
        ])->get();

        if ($search) {
            $needle = mb_strtolower($search);

            $products = $products->filter(function (Product $product) use ($needle) {
                return str_contains(mb_strtolower($product->name), $needle)
                    || ($product->code && str_contains(mb_strtolower($product->code), $needle))
                    || ($product->barcode && str_contains(mb_strtolower($product->barcode), $needle));
            })->values();
        }

        if (isset(self::SORTABLE[$sort])) {
            $key = self::SORTABLE[$sort];
            $callback = $key === 'quantity'
                ? fn (Product $product) => $product->stocks->first()?->quantity ?? 0
                : fn (Product $product) => data_get($product, $key);

            $products = $direction === 'desc'
                ? $products->sortByDesc($callback)->values()
                : $products->sortBy($callback)->values();
        }

        if ($perPage === 'all') {
            return $products;
        }

        $paginator = new LengthAwarePaginator(
            $products->forPage($page, (int) $perPage)->values(),
            $products->count(),
            (int) $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()],
        );

        return $paginator->withQueryString();
    }
}
