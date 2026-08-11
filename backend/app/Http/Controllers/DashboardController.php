<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $activeSales = Sale::where('status', '!=', 'cancelled');

        $salesToday = (clone $activeSales)->whereDate('created_at', today());
        $salesMonth = (clone $activeSales)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $lowStockProducts = Product::where('active', true)
            ->whereColumn('quantity', '<=', 'min_stock')
            ->orderBy('quantity')
            ->get();

        $stockValue = Product::where('active', true)->get()
            ->sum(fn (Product $product) => $product->quantity * $product->cost_price);

        return view('dashboard', [
            'salesTodayCount' => $salesToday->count(),
            'salesTodayTotal' => $salesToday->sum('total'),
            'salesMonthCount' => $salesMonth->count(),
            'salesMonthTotal' => $salesMonth->sum('total'),
            'lowStockProducts' => $lowStockProducts,
            'stockValue' => $stockValue,
            'recentMovements' => StockMovement::with('product')->latest()->limit(8)->get(),
        ]);
    }
}
