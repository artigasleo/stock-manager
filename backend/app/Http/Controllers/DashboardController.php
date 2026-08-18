<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Unit;
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

    public function __invoke(): View
    {
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

        return view('dashboard', [
            'salesTodayCount' => $salesToday->count(),
            'salesTodayTotal' => $salesToday->sum('total'),
            'salesMonthCount' => $salesMonth->count(),
            'salesMonthTotal' => $salesMonth->sum('total'),
            'lowStockStocks' => $lowStockStocks,
            'stockValue' => $stockValue,
            'recentMovements' => StockMovement::with('product')->latest()->limit(8)->get(),
        ]);
    }
}
