<?php

namespace TmrEcosystem\Sales\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\SalesOrder;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\SalesOrderLine;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\Customer;
use TmrEcosystem\Sales\Application\Actions\ConfirmSalesOrderAction;

class SalesOrderController extends Controller
{
    public function index()
    {
        $orders = SalesOrder::with('customer')
            ->orderByDesc('id')
            ->paginate(10);

        return Inertia::render('Sales/Index', ['orders' => $orders]);
    }

    public function create()
    {
        return Inertia::render('Sales/Create', [
            'customers' => Customer::where('is_active', true)->get(['id', 'name']),
            // ส่งสินค้าพร้อมราคาขาย (Price)
            'items' => InventoryItem::with('uom')->where('is_active', true)->get()->map(function($item) {
                return [
                    'id' => $item->id,
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'uom_name' => $item->uom->symbol,
                    'price' => $item->price, // *ใช้ราคาขาย*
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:sales_customers,id',
            'date_order' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.price_unit' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $so = SalesOrder::create([
                'uuid' => Str::uuid(),
                'code' => 'SO-' . now()->format('Ymd-His'),
                'customer_id' => $validated['customer_id'],
                'date_order' => $validated['date_order'],
                'status' => 'draft',
                'total_amount' => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['price_unit'];
                $total += $subtotal;

                $product = InventoryItem::find($item['item_id']);

                SalesOrderLine::create([
                    'order_id' => $so->id,
                    'item_id' => $item['item_id'],
                    'uom_id' => $product->uom_id,
                    'quantity' => $item['quantity'],
                    'price_unit' => $item['price_unit'],
                    'subtotal' => $subtotal,
                ]);
            }
            $so->update(['total_amount' => $total]);
        });

        return to_route('sales.orders.index')->with('success', 'Sales Order created.');
    }

    public function show($id)
    {
        $order = SalesOrder::with(['customer', 'lines.item.uom'])->findOrFail($id);
        return Inertia::render('Sales/Show', ['order' => $order]);
    }

    public function confirm($id, ConfirmSalesOrderAction $action)
    {
        $order = SalesOrder::findOrFail($id);
        try {
            $action->execute($order);
            return back()->with('success', 'Order confirmed. Delivery created.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
