<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;
use TmrEcosystem\Inventory\Application\Actions\ProcessStockMoveAction;
use TmrEcosystem\Inventory\Application\Services\DocumentNumberService; // ✅ Import Service

class OperationsController extends Controller
{
    public function index($type)
    {
        $transfers = StockTransfer::with(['sourceLocation', 'destinationLocation'])
            ->withCount('moves')
            ->where('type', $type) // filter ตาม URL (incoming/outgoing/picking...)
            ->orderByDesc('id')
            ->paginate(10);

        return Inertia::render('Inventory/Operations/Index', [
            'type' => $type,
            'transfers' => $transfers,
        ]);
    }

    public function show($id)
    {
        $transfer = StockTransfer::with(['moves.item.uom', 'sourceLocation', 'destinationLocation'])
            ->findOrFail($id);

        return Inertia::render('Inventory/Operations/Show', [
            'transfer' => $transfer
        ]);
    }

    public function validateTransfer($id, ProcessStockMoveAction $processAction, DocumentNumberService $docService)
    {
        $transfer = StockTransfer::with('moves')->findOrFail($id);

        // 1. Loop Validate ทุก Move (ตัดสต็อกจริง)
        foreach ($transfer->moves as $move) {
            if ($move->state !== 'done') {
                // ถ้ายังไม่ได้กรอกยอดรับ ให้ถือว่ารับครบตามแผน
                if ($move->quantity_done == 0) {
                    $move->quantity_done = $move->quantity_demand;
                }
                $move->save();
                $processAction->execute($move);
            }
        }

        $transfer->update(['status' => 'done']);

        // 2. 🔥 TRIGGER CHAIN: สร้างเอกสารใบถัดไป (Picking -> Pack -> Out)
        $this->createChainedTransfer($transfer, $docService);

        return back()->with('success', 'Document Validated & Next Step Created.');
    }

    /**
     * ฟังก์ชันสร้างเอกสารใบถัดไปตาม Flow Automation
     */
    protected function createChainedTransfer(StockTransfer $completedTransfer, DocumentNumberService $docService)
    {
        $nextType = '';
        $sourceLocId = null;
        $destLocId = null;

        // --- กำหนดเงื่อนไขการไปต่อ ---
        if ($completedTransfer->type === 'picking') {
            // Flow: Picking เสร็จ -> ไป Pack
            // ของอยู่ที่: Packing Zone (ปลายทางของ Picking) -> ส่งไป: Output Zone
            $nextType = 'packing';
            $sourceLocId = $completedTransfer->destination_location_id;

            // หา Location: Output Zone
            $destLocId = InventoryLocation::where('name', 'Output Zone')->value('id');

        } elseif ($completedTransfer->type === 'packing') {
            // Flow: Pack เสร็จ -> ไป Delivery (Outgoing)
            // ของอยู่ที่: Output Zone (ปลายทางของ Pack) -> ส่งไป: ลูกค้า
            $nextType = 'outgoing';
            $sourceLocId = $completedTransfer->destination_location_id;

            // หา Location: Customer (ใช้ Logic เดียวกับตอนสร้าง SO)
            $destLocId = InventoryLocation::where('usage', 'customer')->value('id');

        } else {
            // กรณีอื่นๆ (Incoming, Outgoing จบแล้ว) ไม่ต้องทำต่อ
            return;
        }

        // ป้องกัน Error หากหา Location ไม่เจอ
        if (!$sourceLocId || !$destLocId) return;

        // 1. สร้าง Header เอกสารใหม่
        $newTransfer = StockTransfer::create([
            'uuid' => Str::uuid(),
            'reference' => $docService->generateNextNumber($nextType), // Gen เลขใหม่ (PACK.., OUT..)
            'type' => $nextType,
            'source_location_id' => $sourceLocId,
            'destination_location_id' => $destLocId,
            'contact_id' => $completedTransfer->contact_id,
            'status' => 'ready', // สถานะพร้อมทำ
            'scheduled_date' => now(),
        ]);

        // 2. Copy รายการสินค้า (Moves) มาสร้างใหม่
        foreach ($completedTransfer->moves as $prevMove) {
            StockMove::create([
                'uuid' => Str::uuid(),
                'transfer_id' => $newTransfer->id,
                'item_id' => $prevMove->item_id,
                'uom_id' => $prevMove->uom_id,

                // Location ต้องเปลี่ยนตาม Step ใหม่
                'source_location_id' => $sourceLocId,
                'destination_location_id' => $destLocId,

                // เอาจำนวนที่ทำเสร็จจริง (Done) จากใบก่อนหน้า มาเป็น Demand ของใบนี้
                'quantity_demand' => $prevMove->quantity_done,
                'quantity_done' => 0, // เริ่มต้นยังไม่ได้ทำ
                'state' => 'confirmed', // สถานะรอทำ

                // ผูก Reference เดิม (SO) เพื่อให้ Trace กลับได้
                'reference_type' => $prevMove->reference_type,
                'reference_id' => $prevMove->reference_id,

                'date_expected' => now(),
            ]);
        }
    }
}
