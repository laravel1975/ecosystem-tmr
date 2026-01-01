<?php

namespace TmrEcosystem\Purchase\Application\Actions;

use Illuminate\Support\Facades\DB;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\PurchaseOrder;
use TmrEcosystem\Inventory\Application\Actions\CreateStockMoveAction;
use TmrEcosystem\Inventory\Application\DTOs\StockMoveData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;

class ConfirmPurchaseOrderAction
{
    public function __construct(
        protected CreateStockMoveAction $createStockMoveAction
    ) {}

    public function execute(PurchaseOrder $po): void
    {
        // 1. Validation: ต้องเป็น Draft เท่านั้นถึงจะ Confirm ได้
        if ($po->status !== 'draft') {
            throw new \Exception("Purchase Order {$po->code} is already confirmed or cancelled.");
        }

        // 2. ใช้ Transaction เพื่อความชัวร์ (Data Consistency)
        DB::transaction(function () use ($po) {

            // หา Location ต้นทาง (Supplier) และ ปลายทาง (Warehouse)
            // *ในระบบจริงอาจจะเลือกจากหน้า UI หรือผูกกับ Vendor*
            $sourceLoc = InventoryLocation::where('usage', 'supplier')->firstOrFail();
            $destLoc = InventoryLocation::where('usage', 'internal')->firstOrFail();

            // 3. วนลูปสร้าง Stock Move ตามรายการสินค้า
            foreach ($po->lines as $line) {

                $moveData = new StockMoveData(
                    uuid: null,
                    item_id: $line->item_id,
                    source_location_id: $sourceLoc->id,     // มาจาก Vendor
                    destination_location_id: $destLoc->id,  // เข้า Stock เรา
                    quantity_demand: $line->quantity,       // ยอดที่สั่งซื้อ
                    quantity_done: 0,                       // ยังไม่ได้รับของ (Done = 0)
                    state: 'confirmed',                     // สถานะ = รอรับของ (Confirmed/Assigned)
                    batch_number: null,
                    date_expected: $po->date_expected ?? now()
                );

                $move = $this->createStockMoveAction->execute($moveData);

                // (Optional) เชื่อมโยงกลับว่า Move นี้มาจาก PO ใบไหน
                // ถ้าตาราง StockMove มี field 'origin' หรือ Polymorphic relation
                // $move->update(['origin' => $po->code]);
            }

            // 4. อัปเดตสถานะ PO
            $po->update(['status' => 'confirmed']);
        });
    }
}
