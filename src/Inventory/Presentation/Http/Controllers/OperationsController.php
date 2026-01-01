<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use TmrEcosystem\Inventory\Application\Actions\ProcessStockMoveAction;

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

    public function validateTransfer($id, ProcessStockMoveAction $processAction)
    {
        $transfer = StockTransfer::with('moves')->findOrFail($id);

        // Loop Validate ทุก Move
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

        return back()->with('success', 'Document Validated Successfully.');
    }
}
