<?php

namespace TmrEcosystem\Purchase\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\PurchaseOrder;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\PurchaseOrderLine;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\Vendor;
use TmrEcosystem\Purchase\Application\Actions\ConfirmPurchaseOrderAction;

class PurchaseOrderController extends Controller
{
    // แสดงรายการ PO
    public function index()
    {
        $orders = PurchaseOrder::with('vendor')
            ->orderByDesc('id')
            ->paginate(10);

        return Inertia::render('Purchase/Index', [
            'orders' => $orders
        ]);
    }

    // หน้าฟอร์มสร้าง PO
    public function create()
    {
        return Inertia::render('Purchase/Create', [
            'vendors' => Vendor::where('is_active', true)->get(['id', 'name']),
            'items' => InventoryItem::with('uom')->where('is_active', true)->get()->map(function($item) {
                return [
                    'id' => $item->id,
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'uom_id' => $item->uom_id,
                    'uom_name' => $item->uom->symbol,
                    'cost' => $item->cost, // ราคาต้นทุนล่าสุด
                ];
            }),
        ]);
    }

    // บันทึก PO (Draft)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:purchasing_vendors,id',
            'date_order' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.price_unit' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. สร้าง Header
            $po = PurchaseOrder::create([
                'uuid' => Str::uuid(),
                'code' => 'PO-' . now()->format('Ymd-His'), // สร้างเลขรหัสง่ายๆ ไปก่อน
                'vendor_id' => $validated['vendor_id'],
                'date_order' => $validated['date_order'],
                'status' => 'draft',
                'total_amount' => 0, // เดี๋ยวคำนวณใหม่
            ]);

            $totalAmount = 0;

            // 2. สร้าง Lines
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['price_unit'];
                $totalAmount += $subtotal;

                // หา UOM ของสินค้า (ในระบบจริง User อาจเปลี่ยนหน่วยได้ แต่ตอนนี้เอาตามสินค้าไปก่อน)
                $product = InventoryItem::find($item['item_id']);

                PurchaseOrderLine::create([
                    'order_id' => $po->id,
                    'item_id' => $item['item_id'],
                    'uom_id' => $product->uom_id,
                    'quantity' => $item['quantity'],
                    'price_unit' => $item['price_unit'],
                    'subtotal' => $subtotal,
                    'qty_received' => 0,
                ]);
            }

            // อัปเดตยอดรวม
            $po->update(['total_amount' => $totalAmount]);
        });

        return to_route('purchase.orders.index')->with('success', 'Purchase Order created.');
    }

    // หน้าดูรายละเอียด PO
    public function show($id)
    {
        $order = PurchaseOrder::with(['vendor', 'lines.item.uom'])->findOrFail($id);

        return Inertia::render('Purchase/Show', [
            'order' => $order
        ]);
    }

    // ปุ่ม Confirm
    public function confirm($id, ConfirmPurchaseOrderAction $action)
    {
        $order = PurchaseOrder::with('lines')->findOrFail($id);

        try {
            $action->execute($order);
            return back()->with('success', 'Purchase Order confirmed and Receipt created.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
