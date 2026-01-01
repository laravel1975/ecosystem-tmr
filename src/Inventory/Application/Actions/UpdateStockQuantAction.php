<?php

namespace TmrEcosystem\Inventory\Application\Actions;

use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockQuant;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;

class UpdateStockQuantAction
{
    /**
     * อัปเดตยอดคงเหลือใน Location นั้นๆ
     * @param int $itemId
     * @param int $locationId
     * @param float $quantity (บวกคือเพิ่ม, ลบคือลด)
     */
    public function execute(int $itemId, int $locationId, float $quantity): void
    {
        // 1. หา Record เดิม หรือสร้างใหม่ถ้ายังไม่มี (Snapshot)
        $quant = StockQuant::firstOrNew([
            'item_id' => $itemId,
            'location_id' => $locationId,
        ]);

        // 2. คำนวณยอดใหม่
        $quant->quantity = ($quant->quantity ?? 0) + $quantity;

        // 3. (Optional) ป้องกันยอดติดลบสำหรับ Internal Location (คลังจริง)
        // ถ้าเป็น Virtual Location (Supplier/Customer) ให้ติดลบได้ตามหลักบัญชี
        $location = InventoryLocation::find($locationId);
        if ($location->usage === 'internal' && $quant->quantity < 0) {
             // คุณอาจเลือกที่จะ throw Exception ที่นี่ถ้าระบบซีเรียสเรื่องสต็อกติดลบ
             // throw new \Exception("Stock cannot be negative in internal location: {$location->name}");
        }

        $quant->save();
    }
}
