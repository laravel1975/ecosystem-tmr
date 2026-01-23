<?php

namespace TmrEcosystem\Sales\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\SalesOrder;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\Customer;

class SalesDashboardController extends Controller
{
    public function index()
    {
        // 1. สรุปยอดขายรายเดือน (6 เดือนล่าสุด)
        $monthlySales = SalesOrder::select(
            DB::raw('DATE_FORMAT(date_order, "%Y-%m") as month'),
            DB::raw('SUM(total_amount) as total')
        )
        ->where('status', '!=', 'draft')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(6)
        ->get();

        // 2. ข้อมูล KPIs
        $stats = [
            'total_sales' => SalesOrder::where('status', 'confirmed')->sum('total_amount'),
            'order_count' => SalesOrder::count(),
            'customer_count' => Customer::where('is_active', true)->count(),
            'pending_orders' => SalesOrder::where('status', 'draft')->count(),
        ];

        // 3. ออเดอร์ล่าสุด 5 รายการ
        $recentOrders = SalesOrder::with('customer')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return Inertia::render('Sales/Dashboard', [
            'stats' => $stats,
            'monthlySales' => $monthlySales,
            'recentOrders' => $recentOrders,
        ]);
    }
}
