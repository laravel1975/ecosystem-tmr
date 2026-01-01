<?php

namespace TmrEcosystem\Purchase\Application\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\PurchaseOrder;
use TmrEcosystem\Inventory\Application\Actions\CreateStockMoveAction;
use TmrEcosystem\Inventory\Application\DTOs\StockMoveData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Application\Services\DocumentNumberService; // Inject Service

class ConfirmPurchaseOrderAction
{
    public function __construct(
        protected CreateStockMoveAction $createStockMoveAction,
        protected DocumentNumberService $docService
    ) {}

    public function execute(PurchaseOrder $po): void
    {
        if ($po->status !== 'draft') throw new \Exception("PO is not draft");

        DB::transaction(function () use ($po) {
            $sourceLoc = InventoryLocation::where('usage', 'supplier')->firstOrFail();
            $destLoc = InventoryLocation::where('usage', 'internal')->firstOrFail();

            // 1. สร้าง Header เอกสาร (IN26-xxxxxx)
            $transfer = StockTransfer::create([
                'uuid' => Str::uuid(),
                'reference' => $this->docService->generateNextNumber('incoming'),
                'type' => 'incoming',
                'source_location_id' => $sourceLoc->id,
                'destination_location_id' => $destLoc->id,
                'contact_id' => $po->vendor_id,
                'status' => 'ready',
                'scheduled_date' => $po->date_expected ?? now(),
            ]);

            // 2. สร้าง Lines (Moves)
            foreach ($po->lines as $line) {
                $moveData = new StockMoveData(
                    uuid: null,
                    item_id: $line->item_id,
                    source_location_id: $sourceLoc->id,
                    destination_location_id: $destLoc->id,
                    quantity_demand: $line->quantity,
                    quantity_done: 0,
                    state: 'confirmed', // รอรับ
                    batch_number: null,
                    date_expected: $po->date_expected ?? now()
                );

                $move = $this->createStockMoveAction->execute($moveData);

                // 3. ผูก Move เข้ากับ Transfer
                $move->transfer_id = $transfer->id;
                $move->reference_type = PurchaseOrder::class;
                $move->reference_id = $po->id;
                $move->save();
            }

            $po->update(['status' => 'confirmed']);
        });
    }
}
