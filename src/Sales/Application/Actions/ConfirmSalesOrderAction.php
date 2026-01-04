<?php

namespace TmrEcosystem\Sales\Application\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\SalesOrder;
use TmrEcosystem\Inventory\Application\Actions\CreateStockMoveAction;
use TmrEcosystem\Inventory\Application\DTOs\StockMoveData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Application\Services\DocumentNumberService;

class ConfirmSalesOrderAction
{
    public function __construct(
        protected CreateStockMoveAction $createStockMoveAction,
        protected DocumentNumberService $docService
    ) {}

    public function execute(SalesOrder $order): void
    {
        // 1. Validation
        if ($order->status !== 'draft') {
            throw new \Exception("Sales Order {$order->code} is already confirmed or cancelled.");
        }

        DB::transaction(function () use ($order) {
            // 2. เตรียม Locations ที่ต้องใช้ใน Pipeline (Flow: Stock -> Pack -> Output -> Customer)

            // ต้นทาง 1: คลังสินค้าหลัก (Stock)
            $stockLoc = InventoryLocation::where('usage', 'internal')
                ->where('name', '!=', 'Packing Zone')
                ->where('name', '!=', 'Output Zone')
                ->firstOrFail();

            // จุดพัก 1: โซนแพ็ค (Pack)
            $packLoc = InventoryLocation::where('name', 'Packing Zone')->firstOrFail();

            // จุดพัก 2: โซนรอส่ง (Output)
            $outLoc = InventoryLocation::where('name', 'Output Zone')->firstOrFail();

            // ปลายทาง: ลูกค้า (Customer)
            $custLoc = InventoryLocation::where('usage', 'customer')->firstOrFail();

            // ---------------------------------------------------------
            // Step 1: Picking (Stock -> Pack)
            // สถานะ: Waiting (เปลี่ยนจาก Ready เป็น Waiting เพื่อรอ Check Availability)
            // ---------------------------------------------------------
            $picking = $this->createTransfer(
                type: 'picking',
                status: 'waiting', // ✅ แก้ไข: เริ่มต้นที่ Waiting
                sourceId: $stockLoc->id,
                destId: $packLoc->id,
                order: $order,
                prevId: null // ใบแรก ไม่มีใบก่อนหน้า
            );

            // ---------------------------------------------------------
            // Step 2: Packing (Pack -> Output)
            // สถานะ: Waiting (ต้องรอ Picking เสร็จก่อน ถึงจะแพ็คได้)
            // ---------------------------------------------------------
            $packing = $this->createTransfer(
                type: 'packing',
                status: 'waiting',
                sourceId: $packLoc->id,
                destId: $outLoc->id,
                order: $order,
                prevId: $picking->id // เชื่อมโยง: รอใบ Picking
            );

            // ---------------------------------------------------------
            // Step 3: Delivery (Output -> Customer)
            // สถานะ: Waiting (ต้องรอ Packing เสร็จก่อน ถึงจะส่งได้)
            // ---------------------------------------------------------
            $delivery = $this->createTransfer(
                type: 'outgoing',
                status: 'waiting',
                sourceId: $outLoc->id,
                destId: $custLoc->id,
                order: $order,
                prevId: $packing->id // เชื่อมโยง: รอใบ Packing
            );

            // 3. อัปเดตสถานะ SO เป็น Confirmed
            $order->update(['status' => 'confirmed']);
        });
    }

    /**
     * Helper Function: สร้าง Header และ Lines (Moves) ให้จบในตัว
     */
    private function createTransfer($type, $status, $sourceId, $destId, $order, $prevId)
    {
        // 1. สร้าง Header เอกสาร
        $transfer = StockTransfer::create([
            'uuid' => Str::uuid(),
            'reference' => $this->docService->generateNextNumber($type), // Gen เลขตาม Type (PICK, PACK, OUT)
            'type' => $type,
            'source_location_id' => $sourceId,
            'destination_location_id' => $destId,
            'contact_id' => $order->customer_id,
            'status' => $status, // ready หรือ waiting
            'scheduled_date' => $order->date_delivery_expected ?? now(),
            'previous_transfer_id' => $prevId, // กุญแจสำคัญในการเชื่อมโยง Chain
            'source_document' => $order->code, // บันทึกเลขที่ SO ลงในเอกสาร
        ]);

        // 2. วนลูปสินค้าสร้าง Lines (Stock Moves)
        foreach ($order->lines as $line) {

            // ถ้า Header รอ (Waiting) -> Move ก็ต้องรอ (Waiting) ยังไม่จองของ
            // ถ้า Header พร้อม (Ready) -> Move ยืนยัน (Confirmed) จองของเลย
            $moveState = ($status === 'ready') ? 'confirmed' : 'waiting';

            $moveData = new StockMoveData(
                uuid: null,
                item_id: $line->item_id,
                source_location_id: $sourceId,
                destination_location_id: $destId,
                quantity_demand: $line->quantity,
                quantity_done: 0,
                state: $moveState,
                batch_number: null,
                date_expected: $order->date_delivery_expected ?? now()
            );

            $move = $this->createStockMoveAction->execute($moveData);

            // 3. ผูก Move เข้ากับ Transfer และ Reference (SO)
            $move->transfer_id = $transfer->id;
            $move->reference_type = SalesOrder::class;
            $move->reference_id = $order->id;
            $move->save();
        }

        return $transfer;
    }
}
