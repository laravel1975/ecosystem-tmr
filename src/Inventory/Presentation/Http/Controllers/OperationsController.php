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
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\SalesOrder;

class OperationsController extends Controller
{
    // ... index, show, edit, update (คงเดิม) ...

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
        $transfer = StockTransfer::with([
            'moves.item.uom',
            'sourceLocation',
            'destinationLocation',
            'previousTransfer',
            'nextTransfers'
        ])->findOrFail($id);

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
                    // ลบ Logic Auto-fill เพื่อรองรับ Partial Picking (ตามที่ต้องการ)
                    // if ($move->quantity_done == 0 && !$hasBackorder) { ... }

                    // เช็คว่าต้องทำ Backorder หรือไม่
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

            // 5. Sync Sales Order Status
            $this->syncSalesOrderStatus($transfer);
        });

        return back()->with('success', 'Validated successfully.');
    }

    /**
     * Helper: สร้างชื่อ Backorder แบบสะอาด (Clean Reference)
     * แก้ปัญหา BO-BO-BO โดยการหา Root Name แล้วรันเลขต่อท้ายแทน
     */
    protected function generateBackorderReference($baseReference)
    {
        // 1. ตัดส่วนที่เป็น -BO หรือ -BO-xxx ออกจากชื่อ เพื่อหา "ชื่อต้นฉบับ"
        // เช่น PACK26-001-BO -> PACK26-001
        // เช่น PACK26-001-BO-2 -> PACK26-001
        $rootReference = preg_replace('/-BO(-\d+)?$/', '', $baseReference);

        // 2. เริ่มต้นเช็คจาก -BO ธรรมดา
        $candidate = $rootReference . '-BO';

        // 3. ถ้ามีชื่อนี้แล้ว ให้เริ่มนับ -BO-2, -BO-3 ไปเรื่อยๆ
        $count = 1;
        while (StockTransfer::where('reference', $candidate)->exists()) {
            $count++;
            $candidate = $rootReference . '-BO-' . $count;
        }

        return $candidate;
    }

    protected function createBackorder(StockTransfer $original, DocumentNumberService $docService)
    {
        // ใช้ Helper สร้าง Reference ใหม่เพื่อป้องกัน Duplicate Entry
        $newReference = $this->generateBackorderReference($original->reference);

        $backorder = $original->replicate(['uuid', 'reference', 'status', 'created_at', 'updated_at']);
        $backorder->uuid = Str::uuid();
        $backorder->reference = $newReference; // ✅ ใช้ชื่อที่ไม่ซ้ำ

        // Status Logic: Picking -> Ready, Others -> Waiting
        if ($original->type === 'picking') {
            $backorder->status = 'ready';
        } else {
            $backorder->status = $original->previous_transfer_id ? 'waiting' : 'ready';
        }

        $backorder->is_backorder = true;
        $backorder->previous_transfer_id = $original->previous_transfer_id;
        $backorder->save();

        foreach ($original->moves as $move) {
            $remaining = $move->quantity_demand - $move->quantity_done;

            if ($remaining > 0) {
                // ปรับยอดใบเก่า
                $move->quantity_demand = $move->quantity_done;
                $move->save();

                // สร้าง Move ในใบ Backorder
                $newMove = $move->replicate(['uuid', 'transfer_id', 'state', 'created_at', 'updated_at']);
                $newMove->uuid = Str::uuid();
                $newMove->transfer_id = $backorder->id;
                $newMove->quantity_demand = $remaining;
                $newMove->quantity_done = 0;
                $newMove->state = ($backorder->status === 'ready') ? 'confirmed' : 'waiting';
                $newMove->save();
            }
        }

        return $backorder->load('moves');
    }

    protected function unlockNextTransfers(StockTransfer $currentDoc, ?StockTransfer $currentBackorder, DocumentNumberService $docService)
    {
        $this->syncMainChainDemand($currentDoc);
        $nextDocs = $currentDoc->nextTransfers()->where('status', 'waiting')->get();
        foreach ($nextDocs as $nextDoc) {
            if ($currentBackorder) {
                $this->propagateBackorderChain($nextDoc, $currentBackorder);
            }
            $nextDoc->status = 'ready';
            $nextDoc->save();
            $nextDoc->moves()->update(['state' => 'confirmed']);
        }
    }

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

    protected function propagateBackorderChain(StockTransfer $originalDoc, StockTransfer $parentBackorder)
    {
        // ใช้ Helper สร้าง Reference ใหม่เช่นกัน
        $newReference = $this->generateBackorderReference($originalDoc->reference);

        $newBackorder = $originalDoc->replicate(['uuid', 'reference', 'status', 'created_at', 'updated_at']);
        $newBackorder->uuid = Str::uuid();
        $newBackorder->reference = $newReference; // ✅ ใช้ชื่อที่ไม่ซ้ำ
        $newBackorder->status = 'waiting';
        $newBackorder->is_backorder = true;
        $newBackorder->previous_transfer_id = $parentBackorder->id;
        $newBackorder->save();

        if ($parentBackorder->moves->isEmpty()) $parentBackorder->load('moves');

        foreach ($parentBackorder->moves as $parentMove) {
             $templateMove = StockMove::where('transfer_id', $originalDoc->id)->where('item_id', $parentMove->item_id)->first();
             if ($templateMove) {
                 $newMove = $templateMove->replicate(['uuid', 'transfer_id', 'state', 'created_at', 'updated_at']);
                 $newMove->uuid = Str::uuid();
                 $newMove->transfer_id = $newBackorder->id;
                 $newMove->quantity_demand = $parentMove->quantity_demand;
                 $newMove->quantity_done = 0;
                 $newMove->state = 'waiting';
                 $newMove->save();
             }
        }

        $downstreamDocs = $originalDoc->nextTransfers;
        if ($downstreamDocs->isNotEmpty()) $newBackorder->load('moves');

        foreach ($downstreamDocs as $downstreamDoc) {
            $this->propagateBackorderChain($downstreamDoc, $newBackorder);
        }
    }

    protected function syncSalesOrderStatus(StockTransfer $transfer)
    {
        if (!$transfer->source_document) return;
        $so = SalesOrder::where('code', $transfer->source_document)->first();
        if (!$so) return;

        $newStatus = match ($transfer->type) {
            'picking' => 'packing',
            'packing' => 'ready_to_ship',
            'outgoing' => 'delivered',
            default => null,
        };

        if ($newStatus) {
            $so->update(['status' => $newStatus]);
        }
    }
}
