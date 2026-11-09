<?php

namespace App\Actions\Report;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GenerateProductsReport
{
    public function execute(Carbon $from, Carbon $to): Collection
    {
        $salesInRange = Sale::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from, $to])
            ->select('id');

        return SaleItem::query()
            ->joinSub($salesInRange, 'period_sales', 'period_sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('sale_items.product_id, sum(sale_items.quantity) as total_quantity, sum(sale_items.quantity * sale_items.unit_price * (1 - sale_items.discount_percent / 100)) as total_revenue')
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_revenue')
            ->with('product.supplier')
            ->get();
    }
}
