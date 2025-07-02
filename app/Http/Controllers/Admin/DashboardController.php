<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Products;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalProducts = Products::count();

        $totalCategories = Products::distinct('product_category_id')->count('product_category_id');

        $todayOrders = Orders::whereDate('created_at', Carbon::today())->count();

        $monthlyIncome = Orders::where('payment_status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_price');

        return response()->json([
            'total_products' => $totalProducts,
            'total_categories' => $totalCategories,
            'today_orders' => $todayOrders,
            'monthly_income' => $monthlyIncome,
        ]);
    }

    public function latestOrders()
    {
        $latestOrders = Orders::with(['user', 'details.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'data' => $latestOrders,
        ]);
    }
}
