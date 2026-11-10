<?php

namespace App\Actions\Report;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class GenerateProductsReport
{
    public function execute(
        Carbon $from,
        Carbon $to,
        ?string $search = null,
        int|string $perPage = 25,
        int $page = 1,
    ): Collection|LengthAwarePaginatorContract {
        $salesInRange = Sale::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from, $to])
            ->select('id');

        $query = SaleItem::query()
            ->joinSub($salesInRange, 'period_sales', 'period_sales.id', '=', 'sale_items.sale_id')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->selectRaw('sale_items.product_id, sum(sale_items.quantity) as total_quantity, sum(sale_items.quantity * sale_items.unit_price * (1 - sale_items.discount_percent / 100)) as total_revenue')
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_revenue')
            ->with('product.supplier');

        if ($perPage === 'all') {
            return $query->get();
        }

        return $query->paginate((int) $perPage, ['*'], 'page', $page)->withQueryString();
    }
}
