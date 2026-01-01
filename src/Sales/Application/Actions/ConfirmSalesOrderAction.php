<?php

namespace TmrEcosystem\Sales\Application\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\SalesOrder;
use TmrEcosystem\Inventory\Application\Actions\CreateStockMoveAction;
use TmrEcosystem\Inventory\Application\DTOs\StockMoveData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Application\Services\DocumentNumberService; // <--- Import Service

class ConfirmSalesOrderAction
{
    public function __construct(
        protected CreateStockMoveAction $createStockMoveAction,
        protected DocumentNumberService $docService // <--- Inject Service
    ) {}

    public function execute(SalesOrder $order): void
    {
        // 1. Validation
        if ($order->status !== 'draft') {
            throw new \Exception("Sales Order {$order->code} is already confirmed or cancelled.");
        }

        DB::transaction(function () use ($order) {

            // 2. กำหนดทิศทาง (Internal -> Customer)
            $sourceLoc = InventoryLocation::where('usage', 'internal')->firstOrFail();
            $destLoc = InventoryLocation::where('usage', 'customer')->firstOrFail();

            // 3. ✅ สร้าง Header เอกสาร (OUT26-xxxxxx)
            $transfer = StockTransfer::create([
                'uuid' => Str::uuid(),
                'reference' => $this->docService->generateNextNumber('outgoing'), // Generate เลข OUT
                'type' => 'outgoing',
                'source_location_id' => $sourceLoc->id,
                'destination_location_id' => $destLoc->id,
                'contact_id' => $order->customer_id, // เก็บ Customer ID
                'status' => 'ready', // พร้อมส่ง
                'scheduled_date' => $order->date_delivery_expected ?? now(),
            ]);

            // 4. วนลูปสินค้าสร้าง Lines
            foreach ($order->lines as $line) {

                $moveData = new StockMoveData(
                    uuid: null,
                    item_id: $line->item_id,
                    source_location_id: $sourceLoc->id,
                    destination_location_id: $destLoc->id,
                    quantity_demand: $line->quantity,
                    quantity_done: 0,
                    state: 'confirmed',
                    batch_number: null,
                    date_expected: $order->date_delivery_expected ?? now()
                );

                $move = $this->createStockMoveAction->execute($moveData);

                // 5. ✅ ผูก Move เข้ากับ Transfer Header
                $move->transfer_id = $transfer->id;
                $move->reference_type = SalesOrder::class;
                $move->reference_id = $order->id;
                $move->save();
            }

            // 6. อัปเดตสถานะ SO
            $order->update(['status' => 'confirmed']);
        });
    }
}
