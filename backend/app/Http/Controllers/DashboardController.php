<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:dashboard.view'),
        ];
    }

    public function __invoke(Request $request): View
    {
        $period = $request->string('period')->value() === 'day' ? 'day' : 'month';

        $unit = Unit::default();

        $activeSales = Sale::where('status', '!=', 'cancelled');

        $salesToday = (clone $activeSales)->whereDate('created_at', today());
        $salesMonth = (clone $activeSales)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $activeStocks = ProductStock::where('unit_id', $unit->id)
            ->whereHas('product', fn ($query) => $query->where('active', true))
            ->with('product');

        $lowStockStocks = (clone $activeStocks)
            ->whereColumn('quantity', '<=', 'min_stock')
            ->orderBy('quantity')
            ->get();

        $stockValue = (clone $activeStocks)->get()
            ->sum(fn (ProductStock $stock) => $stock->quantity * $stock->product->cost_price);

        $salesForPeriod = $period === 'day' ? $salesToday : $salesMonth;

        $paymentMethodBreakdown = (clone $salesForPeriod)
            ->selectRaw('payment_method, count(*) as count, sum(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $topSellingProducts = SaleItem::query()
            ->joinSub((clone $salesForPeriod)->select('id'), 'period_sales', 'period_sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('sale_items.product_id, sum(sale_items.quantity) as total_quantity, sum(sale_items.quantity * sale_items.unit_price) as total_revenue')
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_quantity')
            ->with('product')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'salesTodayCount' => $salesToday->count(),
            'salesTodayTotal' => $salesToday->sum('total'),
            'salesMonthCount' => $salesMonth->count(),
            'salesMonthTotal' => $salesMonth->sum('total'),
            'lowStockStocks' => $lowStockStocks,
            'stockValue' => $stockValue,
            'period' => $period,
            'paymentMethodBreakdown' => $paymentMethodBreakdown,
            'topSellingProducts' => $topSellingProducts,
            'recentMovements' => StockMovement::with('product')->latest()->limit(8)->get(),
        ]);
    }
}
