<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use TmrEcosystem\Inventory\Application\Actions\CreateStockMoveAction;
use TmrEcosystem\Inventory\Application\Actions\ProcessStockMoveAction;
use TmrEcosystem\Inventory\Application\DTOs\StockMoveData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;

class StockMovementController extends Controller
{
    public function createReceive()
    {
        return Inertia::render('Stock/Receive', [
            // ส่งข้อมูล Items ไปให้เลือก
            'items' => InventoryItem::with('uom')->where('is_active', true)->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'uom' => $item->uom->symbol,
                ];
            }),
            // Source: ต้องเป็น Vendor Locations
            'source_locations' => InventoryLocation::where('usage', 'supplier')->get(['id', 'name', 'code']),
            // Destination: ต้องเป็น Internal Locations (คลังของเรา)
            'destination_locations' => InventoryLocation::where('usage', 'internal')->get(['id', 'name', 'code']),
        ]);
    }

    public function storeReceive(
        Request $request,
        CreateStockMoveAction $createAction,
        ProcessStockMoveAction $processAction
    ) {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'source_location_id' => 'required|exists:inventory_locations,id',
            'destination_location_id' => 'required|exists:inventory_locations,id',
            'quantity' => 'required|numeric|min:0.01',
            'batch_number' => 'nullable|string|max:255',
        ]);

        // 1. Prepare DTO
        $data = new StockMoveData(
            uuid: null,
            item_id: $validated['item_id'],
            source_location_id: $validated['source_location_id'],
            destination_location_id: $validated['destination_location_id'],
            quantity_demand: $validated['quantity'],
            quantity_done: $validated['quantity'], // รับจริงตามจำนวนที่ระบุ
            state: 'draft',
            batch_number: $validated['batch_number'] ?? null,
            date_expected: now()
        );

        // 2. Create Draft Move
        $move = $createAction->execute($data);

        // 3. Process Immediately (Direct Receipt)
        // ในระบบจริงอาจจะแยกขั้นตอนนี้ แต่สำหรับการรับของด่วน เราทำ Auto-validate เลย
        $processAction->execute($move);

        return to_route('inventory.dashboard')->with('success', 'Stock received successfully.');
    }

    public function createDelivery()
    {
        return Inertia::render('Stock/Delivery', [
            'items' => InventoryItem::with('uom')
                ->where('is_active', true)
                ->get() // ในอนาคตควร filter เฉพาะที่มี stock > 0
                ->map(function ($item) {
                    // หา stock รวมมาแสดงด้วยเพื่อให้รู้ว่ามีของให้ตัดไหม
                    $stock = $item->stockQuants()
                        ->whereHas('location', fn($q) => $q->where('usage', 'internal'))
                        ->sum('quantity');

                    return [
                        'id' => $item->id,
                        'sku' => $item->sku,
                        'name' => $item->name,
                        'uom' => $item->uom->symbol,
                        'on_hand' => $stock,
                    ];
                }),
            // Source: คลังของเรา (Internal)
            'source_locations' => InventoryLocation::where('usage', 'internal')->get(['id', 'name', 'code']),
            // Destination: ลูกค้า (Customer)
            'destination_locations' => InventoryLocation::where('usage', 'customer')->get(['id', 'name', 'code']),
        ]);
    }

    public function storeDelivery(
        Request $request,
        CreateStockMoveAction $createAction,
        ProcessStockMoveAction $processAction
    ) {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'source_location_id' => 'required|exists:inventory_locations,id',
            'destination_location_id' => 'required|exists:inventory_locations,id',
            'quantity' => 'required|numeric|min:0.01',
            'batch_number' => 'nullable|string|max:255',
        ]);

        // **Advanced Check:** เช็คก่อนว่ามีของพอให้ตัดไหม? (Optional)
        // เพื่อความง่ายในขั้นนี้ เราจะปล่อยให้ตัดไปก่อน (ถ้าติดลบแสดงว่า User มั่ว)
        // หรือถ้าต้องการ strict ให้เพิ่ม Logic เช็ค StockQuant ตรงนี้

        $data = new StockMoveData(
            uuid: null,
            item_id: $validated['item_id'],
            source_location_id: $validated['source_location_id'], // ออกจากคลัง
            destination_location_id: $validated['destination_location_id'], // ไปลูกค้า
            quantity_demand: $validated['quantity'],
            quantity_done: $validated['quantity'],
            state: 'draft',
            batch_number: $validated['batch_number'] ?? null,
            date_expected: now()
        );

        $move = $createAction->execute($data);
        $processAction->execute($move);

        return to_route('inventory.dashboard')->with('success', 'Stock delivered successfully.');
    }

    public function validateMove($id, ProcessStockMoveAction $processAction)
    {
        // 1. หา Move ที่ต้องการรับ
        $move = StockMove::findOrFail($id);

        // 2. ตั้งค่าว่ารับของครบตามจำนวนที่สั่ง (quantity_demand)
        // (ในระบบจริงอาจจะให้ user แก้ตัวเลขได้ถ้ารับของไม่ครบ)
        $move->quantity_done = $move->quantity_demand;
        $move->save();

        // 3. รัน Process เพื่อตัดสต็อกจริง (เปลี่ยน state เป็น done)
        $processAction->execute($move);

        return back()->with('success', 'Stock received successfully.');
    }
}
