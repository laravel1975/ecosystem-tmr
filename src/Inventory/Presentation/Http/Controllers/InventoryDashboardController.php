<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockQuant;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        // 1. Overview Cards: นับจำนวนเอกสารที่รออนุมัติ (Waiting/Ready) แยกตามประเภท
        $stats = [
            'incoming' => StockTransfer::where('type', 'incoming')->whereIn('status', ['waiting', 'ready'])->count(),
            'picking'  => StockTransfer::where('type', 'picking')->whereIn('status', ['waiting', 'ready'])->count(),
            'packing'  => StockTransfer::where('type', 'packing')->whereIn('status', ['waiting', 'ready'])->count(),
            'outgoing' => StockTransfer::where('type', 'outgoing')->whereIn('status', ['waiting', 'ready'])->count(),
        ];

        // 2. Stock Balance: ดึงยอดคงเหลือปัจจุบัน (เฉพาะที่มีของ > 0)
        // Group by Item และ Location
        $stocks = StockQuant::with(['item.uom', 'location'])
            ->where('quantity', '>', 0)
            ->orderBy('location_id')
            ->orderBy('item_id')
            ->paginate(20);

        return Inertia::render('Inventory/Dashboard', [
            'stats' => $stats,
            'stocks' => $stocks
        ]);
    }
}
