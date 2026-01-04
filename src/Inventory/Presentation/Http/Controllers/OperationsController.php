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
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockQuant;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\SalesOrder;

class OperationsController extends Controller
{
    // ... index ... (คงเดิม)
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
            'nextTransfers',
            'vendor',
            'customer'
        ])->findOrFail($id);

        if ($transfer->type === 'incoming') {
            $transfer->load('vendor');
        } elseif (in_array($transfer->type, ['outgoing', 'picking', 'packing'])) {
            $transfer->load('customer');
        }

        // ✅ Inject On Hand Quantity: ฝังยอดคงเหลือปัจจุบันไปกับทุก Move เพื่อให้ Frontend ใช้เช็ค
        foreach ($transfer->moves as $move) {
            $quant = StockQuant::where('location_id', $transfer->source_location_id)
                ->where('item_id', $move->item_id)
                ->first();
            // เก็บค่า on_hand ไว้ใน object move (Dynamic Property)
            $move->on_hand = $quant ? $quant->quantity : 0;
        }

        // หาเอกสาร Backorder ที่เกิดจากใบนี้ (ถ้ามี)
        $backorder = StockTransfer::where('origin_transfer_id', $id)->first();

        return Inertia::render('Inventory/Operations/Show', [
            'transfer' => $transfer,
            'backorder' => $backorder
        ]);
    }

    // ✅ NEW: ฟังก์ชันสำหรับอัปเดตยอด Done ทีละบรรทัด (Inline Update)
    public function updateMove(Request $request)
    {
        $request->validate([
            'move_id' => 'required|exists:stock_moves,id',
            'quantity_done' => 'required|numeric|min:0'
        ]);

        $move = StockMove::findOrFail($request->move_id);
        $transfer = StockTransfer::find($move->transfer_id);

        // 🛡️ Security Check: ห้ามใส่เกินสต็อกที่มีจริง (Server-side validation)
        $quant = StockQuant::where('location_id', $transfer->source_location_id)
            ->where('item_id', $move->item_id)
            ->first();
        $onHand = $quant ? $quant->quantity : 0;

        if ($request->quantity_done > $onHand) {
            return back()->with('error', "Cannot pick more than available stock ({$onHand}).");
        }

        // Update
        $move->update(['quantity_done' => $request->quantity_done]);

        return back()->with('success', 'Quantity updated.');
    }

    // ... edit, update (Header) ... (คงเดิม)
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

    protected function generateBackorderReference($baseReference)
    {
        $rootReference = preg_replace('/-BO(-\d+)?$/', '', $baseReference);
        $candidate = $rootReference . '-BO';
        $count = 1;
        while (StockTransfer::where('reference', $candidate)->exists()) {
            $count++;
            $candidate = $rootReference . '-BO-' . $count;
        }
        return $candidate;
    }

    public function checkAvailability($id)
    {
        $transfer = StockTransfer::with('moves')->findOrFail($id);

        if ($transfer->status !== 'waiting') {
            return back()->with('error', 'Operation is not in waiting state.');
        }

        // ตัวแปรสำหรับเช็คสถานะภาพรวม
        $hasAnyStock = false;    // มีของบ้างไหม (แม้นิดเดียว)
        $isFullAvailability = true; // ของครบทุกอย่างไหม

        DB::transaction(function () use ($transfer, &$hasAnyStock, &$isFullAvailability) {
            foreach ($transfer->moves as $move) {
                // 1. เช็คของใน Stock (Location ต้นทาง)
                $quant = StockQuant::where('location_id', $transfer->source_location_id)
                    ->where('item_id', $move->item_id)
                    ->first();

                $onHand = $quant ? $quant->quantity : 0;

                // 2. ประเมินสถานะ
                if ($onHand > 0) {
                    $hasAnyStock = true; // เจอของแล้ว! ปล่อยผ่านได้
                }

                if ($onHand < $move->quantity_demand) {
                    $isFullAvailability = false; // เจอรายการที่ของขาด
                }
            }

            // 3. ถ้ามีของบ้าง (แม้จะไม่ครบ) -> ให้สถานะเป็น Ready เพื่อให้เข้าไปหยิบได้
            if ($hasAnyStock) {
                $transfer->update(['status' => 'ready']);
                $transfer->moves()->update(['state' => 'confirmed']);
            }
        });

        // 4. แจ้งเตือนผู้ใช้ให้ชัดเจน
        $transfer->refresh();

        if ($transfer->status === 'ready') {
            if ($isFullAvailability) {
                return back()->with('success', 'All stock is available. Ready to process.');
            } else {
                return back()->with('warning', 'Partial stock available. You can process available items, the rest will be backordered.');
            }
        } else {
            return back()->with('error', 'No stock available for any items.');
        }
    }

    protected function createBackorder(StockTransfer $original, DocumentNumberService $docService)
    {
        $newReference = $this->generateBackorderReference($original->reference);

        $backorder = $original->replicate(['uuid', 'reference', 'status', 'created_at', 'updated_at']);
        $backorder->uuid = Str::uuid();
        $backorder->reference = $newReference;

        if ($original->type === 'picking') {
            $backorder->status = 'ready';
        } else {
            $backorder->status = $original->previous_transfer_id ? 'waiting' : 'ready';
        }

        $backorder->is_backorder = true;
        $backorder->previous_transfer_id = $original->previous_transfer_id;
        $backorder->origin_transfer_id = $original->id; // ✅ Link Backorder
        $backorder->save();

        foreach ($original->moves as $move) {
            $remaining = $move->quantity_demand - $move->quantity_done;

            if ($remaining > 0) {
                $move->quantity_demand = $move->quantity_done;
                $move->save();

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
        $newReference = $this->generateBackorderReference($originalDoc->reference);

        $newBackorder = $originalDoc->replicate(['uuid', 'reference', 'status', 'created_at', 'updated_at']);
        $newBackorder->uuid = Str::uuid();
        $newBackorder->reference = $newReference;
        $newBackorder->status = 'waiting';
        $newBackorder->is_backorder = true;
        $newBackorder->previous_transfer_id = $parentBackorder->id;
        $newBackorder->origin_transfer_id = $originalDoc->id; // ✅ Link Chain
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
