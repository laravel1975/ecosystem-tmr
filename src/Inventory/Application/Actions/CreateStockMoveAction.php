<?php

namespace TmrEcosystem\Inventory\Application\Actions;

use TmrEcosystem\Inventory\Application\DTOs\StockMoveData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem; // <--- อย่าลืมบรรทัดนี้
use Illuminate\Support\Str;

class CreateStockMoveAction
{
    public function execute(StockMoveData $data): StockMove
    {
        // 1. ดึงข้อมูลสินค้าเพื่อเอา uom_id (หน่วยนับหลัก)
        $item = InventoryItem::findOrFail($data->item_id);

        return StockMove::create([
            'uuid' => Str::uuid(),
            'item_id' => $data->item_id,
            'uom_id' => $item->uom_id, // <--- เบันทึกหน่วยนับลงไป
            'source_location_id' => $data->source_location_id,
            'destination_location_id' => $data->destination_location_id,
            'quantity_demand' => $data->quantity_demand,
            'quantity_done' => $data->quantity_done ?? 0,
            'state' => $data->state,
            'batch_number' => $data->batch_number, // รองรับ Lot/Batch (ถ้ามีใน DTO)
            'date_expected' => $data->date_expected,
        ]);
    }
}
