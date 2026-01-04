<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use Barryvdh\DomPDF\Facade\Pdf;

class TransferPrintController extends Controller
{
    public function print($id)
    {
        // 1. ดึงข้อมูลเอกสาร
        $transfer = StockTransfer::with([
            'moves.item.uom',
            'sourceLocation',
            'destinationLocation',
            'vendor',
            'customer'
        ])->findOrFail($id);

        // 2. เลือก View ตามประเภทเอกสาร
        // Picking -> ใบหยิบสินค้า
        // Delivery -> ใบส่งของ
        // Packing -> ใบปะหน้ากล่อง
        $viewName = match ($transfer->type) {
            'picking' => 'inventory.reports.picking_slip',
            'outgoing' => 'inventory.reports.delivery_note',
            default => 'inventory.reports.operation_slip',
        };

        // 3. Setup PDF
        $pdf = Pdf::loadView($viewName, ['transfer' => $transfer]);

        // ตั้งค่ากระดาษ (A4)
        $pdf->setPaper('a4', 'portrait');

        // 4. Stream ออกไป (เปิดใน Browser ก่อนโหลด)
        return $pdf->stream($transfer->reference . '.pdf');
    }
}
