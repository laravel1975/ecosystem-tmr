<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Str;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;
use TmrEcosystem\Inventory\Application\Actions\ProcessStockMoveAction;
use TmrEcosystem\Inventory\Application\Services\DocumentNumberService;

class OperationsController extends Controller
{
    public function index($type)
    {
        // 1. กำหนดความสัมพันธ์ที่จะโหลด (Relation) ตามประเภทเอกสาร
        $contactRelation = ($type === 'incoming') ? 'vendor' : 'customer';

        $transfers = StockTransfer::with(['sourceLocation', 'destinationLocation', $contactRelation]) // โหลด Relation ที่ถูกต้อง
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
        // ดึงข้อมูลออกมาก่อนเพื่อเช็ค Type
        $transfer = StockTransfer::with(['moves.item.uom', 'sourceLocation', 'destinationLocation'])
            ->findOrFail($id);

        // โหลด contact ตาม Type
        if ($transfer->type === 'incoming') {
            $transfer->load('vendor');
        } elseif (in_array($transfer->type, ['outgoing', 'picking', 'packing'])) {
            $transfer->load('customer');
        }

        return Inertia::render('Inventory/Operations/Show', [
            'transfer' => $transfer
        ]);
    }

    public function validateTransfer($id, ProcessStockMoveAction $processAction, DocumentNumberService $docService)
    {
        $transfer = StockTransfer::with('moves')->findOrFail($id);

        if ($transfer->status === 'done') {
            return back()->with('error', 'Document is already validated.');
        }

        // 1. Loop Validate ทุก Move
        foreach ($transfer->moves as $move) {
            if ($move->state !== 'done') {
                if ($move->quantity_done == 0) {
                    $move->quantity_done = $move->quantity_demand;
                }
                $move->save();
                $processAction->execute($move);
            }
        }

        $transfer->update(['status' => 'done']);

        // 2. Trigger Chain Operation
        $nextDoc = $this->createChainedTransfer($transfer, $docService);

        $message = 'Document validated successfully.';
        if ($nextDoc) {
            $message .= " Next operation created: {$nextDoc->reference} ({$nextDoc->type})";
        }

        return back()->with('success', $message);
    }

    protected function createChainedTransfer(StockTransfer $completedTransfer, DocumentNumberService $docService)
    {
        $nextType = '';
        $sourceLocId = null;
        $destLocId = null;

        if ($completedTransfer->type === 'picking') {
            $nextType = 'packing';
            $sourceLocId = $completedTransfer->destination_location_id;
            $destLocId = InventoryLocation::where('name', 'Output Zone')->value('id');

        } elseif ($completedTransfer->type === 'packing') {
            $nextType = 'outgoing';
            $sourceLocId = $completedTransfer->destination_location_id;
            $destLocId = InventoryLocation::where('usage', 'customer')->value('id');

        } else {
            return null;
        }

        if (!$sourceLocId || !$destLocId) return null;

        $newTransfer = StockTransfer::create([
            'uuid' => Str::uuid(),
            'reference' => $docService->generateNextNumber($nextType),
            'type' => $nextType,
            'source_location_id' => $sourceLocId,
            'destination_location_id' => $destLocId,
            'contact_id' => $completedTransfer->contact_id,
            'status' => 'ready',
            'scheduled_date' => now(),
        ]);

        foreach ($completedTransfer->moves as $prevMove) {
            StockMove::create([
                'uuid' => Str::uuid(),
                'transfer_id' => $newTransfer->id,
                'item_id' => $prevMove->item_id,
                'uom_id' => $prevMove->uom_id,
                'source_location_id' => $sourceLocId,
                'destination_location_id' => $destLocId,
                'quantity_demand' => $prevMove->quantity_done,
                'quantity_done' => 0,
                'state' => 'confirmed',
                'reference_type' => $prevMove->reference_type,
                'reference_id' => $prevMove->reference_id,
                'date_expected' => now(),
            ]);
        }

        return $newTransfer;
    }
}
