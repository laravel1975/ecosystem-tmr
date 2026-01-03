<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;
use TmrEcosystem\Inventory\Application\Actions\ProcessStockMoveAction;
use TmrEcosystem\Inventory\Application\Services\DocumentNumberService;

class OperationsController extends Controller
{
    public function index($type)
    {
        // โหลด Relation ตาม Type
        $contactRelation = ($type === 'incoming') ? 'vendor' : 'customer';

        $transfers = StockTransfer::with(['sourceLocation', 'destinationLocation', $contactRelation])
            ->withCount('moves')
            ->where('type', $type)
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

        if ($transfer->type === 'incoming') {
            $transfer->load('vendor');
        } elseif (in_array($transfer->type, ['outgoing', 'picking', 'packing'])) {
            $transfer->load('customer');
        }

        return Inertia::render('Inventory/Operations/Show', [
            'transfer' => $transfer
        ]);
    }

    /**
     * แสดงหน้าแก้ไขเอกสาร
     */
    public function edit($id)
    {
        $transfer = StockTransfer::with(['moves.item.uom', 'sourceLocation', 'destinationLocation'])
            ->findOrFail($id);

        // โหลด Contact ให้ถูกต้องตาม Type
        if ($transfer->type === 'incoming') {
            $transfer->load('vendor');
        } elseif (in_array($transfer->type, ['outgoing', 'picking', 'packing'])) {
            $transfer->load('customer');
        }

        return Inertia::render('Inventory/Operations/Edit', [
            'transfer' => $transfer
        ]);
    }

    /**
     * บันทึกการแก้ไข
     */
    public function update(Request $request, $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        // ห้ามแก้ถ้า Done ไปแล้ว
        if ($transfer->status === 'done') {
            return back()->with('error', 'Cannot edit validated document.');
        }

        $data = $request->validate([
            'scheduled_date' => 'nullable|date',
            'note' => 'nullable|string',
            'moves' => 'array',
            'moves.*.id' => 'required|exists:stock_moves,id',
            'moves.*.quantity_demand' => 'required|numeric|min:0',
            'moves.*.quantity_done' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($transfer, $data) {
            // 1. Update Header
            $transfer->update([
                'scheduled_date' => $data['scheduled_date'],
                'note' => $data['note'],
            ]);

            // 2. Update Lines (Moves)
            foreach ($data['moves'] as $line) {
                $move = StockMove::find($line['id']);
                if ($move) {
                    $move->update([
                        'quantity_demand' => $line['quantity_demand'],
                        'quantity_done' => $line['quantity_done'], // สำคัญ: เอาไว้กรอกยอดจริงก่อน Validate
                    ]);
                }
            }
        });

        return redirect()->route('inventory.ops.show', $transfer->id)
            ->with('success', 'Document updated successfully.');
    }

    // --------------------------------------------------------------------------
    // 🚀 MASTER LOGIC: Validate & Chain & Backorder
    // --------------------------------------------------------------------------
    public function validateTransfer($id, ProcessStockMoveAction $processAction, DocumentNumberService $docService)
    {
        $transfer = StockTransfer::with(['moves', 'nextTransfers'])->findOrFail($id);

        if ($transfer->status === 'done') {
            return back()->with('error', 'Document is already validated.');
        }

        DB::transaction(function () use ($transfer, $processAction, $docService) {
            $hasBackorder = false;

            // 1. Process Moves (ตัดสต็อก)
            foreach ($transfer->moves as $move) {
                if ($move->state !== 'done') {
                    // ถ้า User ไม่กรอกยอด (0) ให้ถือว่ารับครบตามแผน
                    // แต่ถ้ากรอกมาแต่น้อยกว่า Demand -> คือ Backorder
                    if ($move->quantity_done == 0 && !$hasBackorder) {
                        $move->quantity_done = $move->quantity_demand;
                    }

                    // เช็คว่าต้องทำ Backorder หรือไม่?
                    if ($move->quantity_done < $move->quantity_demand) {
                        $hasBackorder = true;
                    }

                    $move->save();
                    $processAction->execute($move); // ตัดสต็อกจริง (เฉพาะยอด Done)
                }
            }

            // 2. จัดการ Backorder (ถ้ามี)
            // ถ้าของไม่ครบ -> สร้างใบใหม่รอไว้ (Backorder)
            // ใบปัจจุบัน -> จบงาน (Done) ที่ยอดเท่าที่มี
            $currentBackorder = null;
            if ($hasBackorder) {
                $currentBackorder = $this->createBackorder($transfer, $docService);
            }

            // 3. ปลดล็อคเอกสารใบถัดไป (Chain)
            // ส่งยอดที่ทำเสร็จไปให้ใบถัดไป + ส่ง Backorder ไปให้ใบถัดไปสร้างตาม
            $this->unlockNextTransfers($transfer, $currentBackorder, $docService);

            // 4. จบงานใบนี้
            $transfer->update(['status' => 'done']);
        });

        return back()->with('success', 'Validated successfully.');
    }

    /**
     * สร้างเอกสาร Backorder สำหรับยอดที่เหลือ
     */
    protected function createBackorder(StockTransfer $original, DocumentNumberService $docService)
    {
        // 1. Replicate Header
        $backorder = $original->replicate(['uuid', 'reference', 'status', 'created_at', 'updated_at']);
        $backorder->uuid = Str::uuid();

        // Gen เลขใหม่: PICK26-001-BO
        $backorder->reference = $original->reference . '-BO';

        // สถานะ: ถ้าใบเดิม Ready -> ใบใหม่ก็ Ready รอทำต่อเลย
        // แต่ถ้าใบเดิม Waiting -> ใบใหม่ก็ Waiting
        $backorder->status = $original->status;
        $backorder->is_backorder = true;
        $backorder->previous_transfer_id = $original->previous_transfer_id; // ยังรอใบแม่เดียวกัน (ถ้ามี)
        $backorder->save();

        // 2. Process Moves (Split Logic)
        foreach ($original->moves as $move) {
            $remaining = $move->quantity_demand - $move->quantity_done;

            if ($remaining > 0) {
                // 2.1 แก้ไข Move เดิม: ปรับ Demand ลงมาให้เท่ากับที่ทำจริง (เพื่อให้ปิดจอบได้สวยๆ)
                $move->quantity_demand = $move->quantity_done;
                $move->save();

                // 2.2 สร้าง Move ใหม่ใน Backorder: ใส่ยอดที่เหลือ (Remaining)
                $newMove = $move->replicate(['uuid', 'transfer_id', 'state', 'created_at', 'updated_at']);
                $newMove->uuid = Str::uuid();
                $newMove->transfer_id = $backorder->id;
                $newMove->quantity_demand = $remaining;
                $newMove->quantity_done = 0;
                $newMove->state = ($backorder->status === 'ready') ? 'confirmed' : 'waiting';
                $newMove->save();
            }
        }

        return $backorder;
    }

    /**
     * ปลดล็อคใบถัดไป และ Sync ยอดให้ตรงกัน (แก้ไขใหม่: อัปเดตยอดทั้งสายโซ่)
     */
    protected function unlockNextTransfers(StockTransfer $currentDoc, ?StockTransfer $currentBackorder, DocumentNumberService $docService)
    {
        // 1. 🔥 Sync Quantities (Demand) ให้ตรงกันทั้งสายโซ่ (Main Chain)
        // (เช่น Picking Done 80 -> Pack Demand 80 -> Delivery Demand 80)
        $this->syncMainChainDemand($currentDoc);

        // 2. หาใบถัดไปที่รออยู่ (Waiting)
        $nextDocs = $currentDoc->nextTransfers()->where('status', 'waiting')->get();

        foreach ($nextDocs as $nextDoc) {
            // 3. Create Next Backorder & Chain (ถ้ามี Backorder ส่งมา)
            if ($currentBackorder) {
                $this->propagateBackorderChain($nextDoc, $currentBackorder);
            }

            // 4. Unlock: เปลี่ยนสถานะเป็น Ready
            $nextDoc->status = 'ready';
            $nextDoc->save();

            // อัปเดตสถานะ Moves ภายในให้เป็น confirmed (พร้อมทำ)
            $nextDoc->moves()->update(['state' => 'confirmed']);
        }
    }

    /**
     * ✅ ฟังก์ชันใหม่: อัปเดตยอด Demand ของเอกสารใบหลักทั้งสายโซ่
     */
    protected function syncMainChainDemand(StockTransfer $sourceDoc)
    {
        $nextDocs = $sourceDoc->nextTransfers; // หาใบลูกข่ายทั้งหมด

        foreach ($nextDocs as $nextDoc) {
            foreach ($sourceDoc->moves as $sourceMove) {
                $nextMove = StockMove::where('transfer_id', $nextDoc->id)
                    ->where('item_id', $sourceMove->item_id)
                    ->first();

                if ($nextMove) {
                     // Logic:
                     // ถ้าเป็นใบแม่ที่เพิ่งจบ (Done) -> ใช้ยอดที่ทำเสร็จจริง (Done)
                     // ถ้าเป็นใบระหว่างทาง (ที่เพิ่งถูกอัปเดต) -> ใช้ยอด Demand ของมันส่งต่อ
                     $qtyToPropagate = ($sourceDoc->status === 'done') ? $sourceMove->quantity_done : $sourceMove->quantity_demand;

                     // อัปเดต Demand ของใบถัดไป
                     if ($nextMove->quantity_demand != $qtyToPropagate) {
                         $nextMove->quantity_demand = $qtyToPropagate;
                         $nextMove->save();
                     }
                }
            }

            // 🔄 Recursive: ส่งต่อไปอัปเดตใบลูกของลูกด้วย (เช่น Pack -> Delivery)
            $this->syncMainChainDemand($nextDoc);
        }
    }

    /**
     * ✅ ฟังก์ชันใหม่: สร้าง Backorder แบบลูกโซ่ (Recursive)
     */
    protected function propagateBackorderChain(StockTransfer $originalDoc, StockTransfer $parentBackorder)
    {
        // 1. Clone Header
        $newBackorder = $originalDoc->replicate(['uuid', 'reference', 'status', 'created_at', 'updated_at']);
        $newBackorder->uuid = Str::uuid();
        $newBackorder->reference = $originalDoc->reference . '-BO';
        $newBackorder->status = 'waiting'; // สถานะรอเสมอ
        $newBackorder->is_backorder = true;
        $newBackorder->previous_transfer_id = $parentBackorder->id;
        $newBackorder->save();

        // 2. Clone Moves
        foreach ($parentBackorder->moves as $parentMove) {
             // หา Move คู่กันในใบ Original
             $templateMove = StockMove::where('transfer_id', $originalDoc->id)
                ->where('item_id', $parentMove->item_id)
                ->first();

             if ($templateMove) {
                 $newMove = $templateMove->replicate(['uuid', 'transfer_id', 'state', 'created_at', 'updated_at']);
                 $newMove->uuid = Str::uuid();
                 $newMove->transfer_id = $newBackorder->id;
                 $newMove->quantity_demand = $parentMove->quantity_demand; // ยอดที่ค้างส่ง
                 $newMove->quantity_done = 0;
                 $newMove->state = 'waiting';
                 $newMove->save();
             }
        }

        // 3. 🔄 RECURSIVE: หาใบถัดไปของ Original แล้วทำซ้ำ
        $downstreamDocs = $originalDoc->nextTransfers;
        foreach ($downstreamDocs as $downstreamDoc) {
            $this->propagateBackorderChain($downstreamDoc, $newBackorder);
        }
    }
}
