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

    public function edit($id)
    {
        $transfer = StockTransfer::with(['moves.item.uom', 'sourceLocation', 'destinationLocation'])
            ->findOrFail($id);

        if ($transfer->type === 'incoming') {
            $transfer->load('vendor');
        } elseif (in_array($transfer->type, ['outgoing', 'picking', 'packing'])) {
            $transfer->load('customer');
        }

        return Inertia::render('Inventory/Operations/Edit', [
            'transfer' => $transfer
        ]);
    }

    public function update(Request $request, $id)
    {
        $transfer = StockTransfer::findOrFail($id);

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
            $transfer->update([
                'scheduled_date' => $data['scheduled_date'],
                'note' => $data['note'],
            ]);

            foreach ($data['moves'] as $line) {
                $move = StockMove::find($line['id']);
                if ($move) {
                    $move->update([
                        'quantity_demand' => $line['quantity_demand'],
                        'quantity_done' => $line['quantity_done'],
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
                    if ($move->quantity_done == 0 && !$hasBackorder) {
                        $move->quantity_done = $move->quantity_demand;
                    }

                    if ($move->quantity_done < $move->quantity_demand) {
                        $hasBackorder = true;
                    }

                    $move->save();
                    $processAction->execute($move);
                }
            }

            // 2. จัดการ Backorder (ถ้ามี)
            $currentBackorder = null;
            if ($hasBackorder) {
                $currentBackorder = $this->createBackorder($transfer, $docService);
            }

            // 3. ปลดล็อคเอกสารใบถัดไป (Chain)
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
        $backorder->reference = $original->reference . '-BO';
        $backorder->status = $original->status;
        $backorder->is_backorder = true;
        $backorder->previous_transfer_id = $original->previous_transfer_id;
        $backorder->save();

        // 2. Process Moves (Split Logic)
        foreach ($original->moves as $move) {
            $remaining = $move->quantity_demand - $move->quantity_done;

            if ($remaining > 0) {
                // แก้ไข Move เดิม
                $move->quantity_demand = $move->quantity_done;
                $move->save();

                // สร้าง Move ใหม่ใน Backorder
                $newMove = $move->replicate(['uuid', 'transfer_id', 'state', 'created_at', 'updated_at']);
                $newMove->uuid = Str::uuid();
                $newMove->transfer_id = $backorder->id;
                $newMove->quantity_demand = $remaining;
                $newMove->quantity_done = 0;
                $newMove->state = ($backorder->status === 'ready') ? 'confirmed' : 'waiting';
                $newMove->save();
            }
        }

        // 🔥 FIX 1: ต้อง Load Moves กลับมาให้ตัวแปรทันที เพื่อให้พร้อมส่งต่อไปยังฟังก์ชันอื่น
        return $backorder->load('moves');
    }

    /**
     * ปลดล็อคใบถัดไป และ Sync ยอดให้ตรงกัน (แก้ไขใหม่: อัปเดตยอดทั้งสายโซ่)
     */
    protected function unlockNextTransfers(StockTransfer $currentDoc, ?StockTransfer $currentBackorder, DocumentNumberService $docService)
    {
        // 1. Sync Quantities
        $this->syncMainChainDemand($currentDoc);

        // 2. หาใบถัดไปที่รออยู่
        $nextDocs = $currentDoc->nextTransfers()->where('status', 'waiting')->get();

        foreach ($nextDocs as $nextDoc) {
            // 3. Create Next Backorder & Chain
            if ($currentBackorder) {
                $this->propagateBackorderChain($nextDoc, $currentBackorder);
            }

            // 4. Unlock
            $nextDoc->status = 'ready';
            $nextDoc->save();
            $nextDoc->moves()->update(['state' => 'confirmed']);
        }
    }

    /**
     * อัปเดตยอด Demand ของเอกสารใบหลักทั้งสายโซ่
     */
    protected function syncMainChainDemand(StockTransfer $sourceDoc)
    {
        $nextDocs = $sourceDoc->nextTransfers;

        foreach ($nextDocs as $nextDoc) {
            foreach ($sourceDoc->moves as $sourceMove) {
                $nextMove = StockMove::where('transfer_id', $nextDoc->id)
                    ->where('item_id', $sourceMove->item_id)
                    ->first();

                if ($nextMove) {
                     $qtyToPropagate = ($sourceDoc->status === 'done') ? $sourceMove->quantity_done : $sourceMove->quantity_demand;

                     if ($nextMove->quantity_demand != $qtyToPropagate) {
                         $nextMove->quantity_demand = $qtyToPropagate;
                         $nextMove->save();
                     }
                }
            }
            $this->syncMainChainDemand($nextDoc);
        }
    }

    /**
     * สร้าง Backorder แบบลูกโซ่ (Recursive)
     */
    protected function propagateBackorderChain(StockTransfer $originalDoc, StockTransfer $parentBackorder)
    {
        // 1. Clone Header
        $newBackorder = $originalDoc->replicate(['uuid', 'reference', 'status', 'created_at', 'updated_at']);
        $newBackorder->uuid = Str::uuid();
        $newBackorder->reference = $originalDoc->reference . '-BO';
        $newBackorder->status = 'waiting';
        $newBackorder->is_backorder = true;
        $newBackorder->previous_transfer_id = $parentBackorder->id;
        $newBackorder->save();

        // 2. Clone Moves
        // (มั่นใจว่า Parent มี Moves เพราะเรา Load มาแล้วจากขั้นตอนก่อนหน้า)
        foreach ($parentBackorder->moves as $parentMove) {
             $templateMove = StockMove::where('transfer_id', $originalDoc->id)
                ->where('item_id', $parentMove->item_id)
                ->first();

             if ($templateMove) {
                 $newMove = $templateMove->replicate(['uuid', 'transfer_id', 'state', 'created_at', 'updated_at']);
                 $newMove->uuid = Str::uuid();
                 $newMove->transfer_id = $newBackorder->id;
                 $newMove->quantity_demand = $parentMove->quantity_demand; // Copy ยอดจาก BO แม่
                 $newMove->quantity_done = 0;
                 $newMove->state = 'waiting';
                 $newMove->save();
             }
        }

        // 3. Recursive
        $downstreamDocs = $originalDoc->nextTransfers;

        // 🔥 FIX 2: Load Moves ให้ใบที่เพิ่งสร้าง ก่อนส่งไปให้ลูกหลาน (เช่น ส่ง Packing-BO ไปสร้าง Delivery-BO)
        if ($downstreamDocs->isNotEmpty()) {
            $newBackorder->load('moves');
        }

        foreach ($downstreamDocs as $downstreamDoc) {
            $this->propagateBackorderChain($downstreamDoc, $newBackorder);
        }
    }
}
