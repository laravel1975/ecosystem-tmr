<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;

class InventoryDashboardController extends Controller
{
    public function __invoke(): Response
    {
        // 1. ดึงข้อมูลสินค้า (เหมือนเดิม)
        $items = InventoryItem::with(['category', 'uom', 'stockQuants' => function ($query) {
            $query->whereHas('location', function ($q) {
                $q->where('usage', 'internal');
            });
        }])->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'category' => $item->category->name ?? '-',
                'uom' => $item->uom->symbol,
                'price' => $item->price,
                'on_hand' => $item->stockQuants->sum('quantity'),
            ];
        });

        // 2. 👇 เพิ่มส่วนนี้: ดึงรายการ "รอรับเข้า" (Incoming Pending)
        // คือ Move ที่สถานะ 'confirmed' และวิ่งเข้าคลังเรา (Internal)
        $incomingMoves = StockMove::with(['item.uom', 'sourceLocation'])
            ->where('state', 'confirmed') // ต้องสะกดถูกต้อง confirmed
            ->whereHas('destinationLocation', function($q) {
                $q->where('usage', 'internal'); // ต้องเป็น internal
            })
            ->orderBy('date_expected')
            ->get()
            ->map(function ($move) {
                return [
                    'id' => $move->id,
                    'item_name' => $move->item->name ?? 'Unknown', // กัน Error
                    'qty' => (float) $move->quantity_demand,
                    'uom' => $move->item->uom->symbol ?? 'Unit',
                    'source' => $move->sourceLocation->name ?? 'Unknown',
                    'date' => $move->date_expected ? $move->date_expected->format('Y-m-d') : '-',
                ];
            });

        // 3. ส่งข้อมูลไปหน้า View
        return Inertia::render('Inventory/Dashboard', [
            'items' => $items,
            'incomingMoves' => $incomingMoves, // <--- ส่งตัวแปรนี้เพิ่ม
        ]);
    }
}
