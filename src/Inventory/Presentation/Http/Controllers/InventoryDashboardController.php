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
        // 1. Items (เหมือนเดิม)
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

        // 2. Incoming Moves (ของเข้า - เหมือนเดิม)
        $incomingMoves = StockMove::with(['item.uom', 'sourceLocation'])
            ->where('state', 'confirmed')
            ->whereHas('destinationLocation', fn($q) => $q->where('usage', 'internal'))
            ->orderBy('date_expected')
            ->get()
            ->map(function ($move) {
                return [
                    'id' => $move->id,
                    'item_name' => $move->item->name ?? 'Unknown',
                    'qty' => (float) $move->quantity_demand,
                    'uom' => $move->item->uom->symbol ?? 'Unit',
                    'source' => $move->sourceLocation->name ?? 'Unknown',
                    'date' => $move->date_expected ? $move->date_expected->format('Y-m-d') : '-',
                ];
            });

        // 3. 👇 เพิ่ม: Outgoing Moves (ของออก - รอส่งลูกค้า)
        $outgoingMoves = StockMove::with(['item.uom', 'destinationLocation'])
            ->where('state', 'confirmed')
            // เงื่อนไข: ออกจาก Internal -> ไป Customer
            ->whereHas('sourceLocation', fn($q) => $q->where('usage', 'internal'))
            ->whereHas('destinationLocation', fn($q) => $q->where('usage', 'customer'))
            ->orderBy('date_expected')
            ->get()
            ->map(function ($move) {
                return [
                    'id' => $move->id,
                    'item_name' => $move->item->name ?? 'Unknown',
                    'qty' => (float) $move->quantity_demand,
                    'uom' => $move->item->uom->symbol ?? 'Unit',
                    'destination' => $move->destinationLocation->name ?? 'Customer',
                    'date' => $move->date_expected ? $move->date_expected->format('Y-m-d') : '-',
                ];
            });

        return Inertia::render('Inventory/Dashboard', [
            'items' => $items,
            'incomingMoves' => $incomingMoves,
            'outgoingMoves' => $outgoingMoves,
        ]);
    }
}
