<?php

namespace TmrEcosystem\Purchase\Application\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\PurchaseOrder;
use TmrEcosystem\Inventory\Application\Actions\CreateStockMoveAction;
use TmrEcosystem\Inventory\Application\DTOs\StockMoveData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Application\Services\DocumentNumberService;

class ConfirmPurchaseOrderAction
{
    public function __construct(
        protected CreateStockMoveAction $createStockMoveAction,
        protected DocumentNumberService $docService
    ) {}

    public function execute(PurchaseOrder $order): void
    {
        // 1. Validation
        if ($order->status !== 'draft') {
            throw new \Exception("Purchase Order {$order->code} is already confirmed.");
        }

        DB::transaction(function () use ($order) {
            // 2. เตรียม Locations (Vendor -> Stock)
            // หา Location ของ Supplier (ต้นทาง)
            $vendorLoc = InventoryLocation::where('usage', 'supplier')->firstOrFail();

            // หา Location ของ Stock เรา (ปลายทาง)
            $stockLoc = InventoryLocation::where('usage', 'internal')
                ->where('name', '!=', 'Packing Zone')
                ->where('name', '!=', 'Output Zone')
                ->firstOrFail();

            // 3. สร้าง Header เอกสาร Receipt (Incoming)
            $transfer = StockTransfer::create([
                'uuid' => Str::uuid(),
                'reference' => $this->docService->generateNextNumber('incoming'), // เช่น IN-20260104-001
                'type' => 'incoming',
                'source_location_id' => $vendorLoc->id,
                'destination_location_id' => $stockLoc->id,
                'contact_id' => $order->vendor_id, // Vendor ID
                'status' => 'ready', // ของมาถึงแล้ว พร้อมตรวจรับทันที
                'scheduled_date' => $order->date_expected ?? now(),
                'source_document' => $order->code, // Link กลับหา PO
            ]);

            // 4. สร้าง Moves (รายการสินค้าที่จะรับ)
            foreach ($order->lines as $line) {
                $moveData = new StockMoveData(
                    uuid: null,
                    item_id: $line->item_id,
                    source_location_id: $vendorLoc->id,
                    destination_location_id: $stockLoc->id,
                    quantity_demand: $line->quantity,
                    quantity_done: 0,
                    state: 'confirmed', // จองพื้นที่/รอรับ
                    batch_number: null,
                    date_expected: $order->date_expected ?? now()
                );

                $move = $this->createStockMoveAction->execute($moveData);

                // ผูก Move เข้ากับ Transfer และ PO
                $move->transfer_id = $transfer->id;
                $move->reference_type = PurchaseOrder::class;
                $move->reference_id = $order->id;
                $move->save();
            }

            // 5. อัปเดตสถานะ PO เป็น Confirmed
            $order->update(['status' => 'confirmed']);
        });
    }
}
