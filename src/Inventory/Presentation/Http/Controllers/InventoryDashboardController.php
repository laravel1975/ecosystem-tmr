<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;

class InventoryDashboardController extends Controller
{
    public function __invoke(): Response
    {
        // ดึงข้อมูลสินค้า พร้อมคำนวณยอดคงเหลือรวม (Sum of Quants in Internal Locations)
        // หมายเหตุ: ใน Production จริง เราอาจใช้ Query Builder หรือ View Table เพื่อความเร็ว
        $items = InventoryItem::with(['category', 'uom', 'stockQuants' => function ($query) {
            $query->whereHas('location', function ($q) {
                $q->where('usage', 'internal'); // เอาเฉพาะคลังจริง
            });
        }])->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'category' => $item->category->name ?? '-',
                'uom' => $item->uom->symbol,
                'price' => $item->price,
                // รวมยอดคงเหลือทุก Internal Location
                'on_hand' => $item->stockQuants->sum('quantity'),
            ];
        });

        return Inertia::render('Inventory/Dashboard', [
            'items' => $items,
        ]);
    }
}
