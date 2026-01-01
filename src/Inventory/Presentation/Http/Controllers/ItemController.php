<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use TmrEcosystem\Inventory\Application\Actions\CreateItemAction;
use TmrEcosystem\Inventory\Application\DTOs\InventoryItemData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryCategory;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryUom;

class ItemController extends Controller
{
    public function create()
    {
        return Inertia::render('Inventory/Items/Create', [
            'categories' => InventoryCategory::select('id', 'name')->get(),
            'uoms' => InventoryUom::select('id', 'symbol', 'name')->get(),
        ]);
    }

    public function store(Request $request, CreateItemAction $action)
    {
        // Validation แบบง่าย (ควรแยก Request Class ใน Production)
        $validated = $request->validate([
            'sku' => 'required|unique:inventory_items,sku',
            'name' => 'required',
            'uom_id' => 'required|exists:inventory_uoms,id',
            'price' => 'numeric|min:0',
            'cost' => 'numeric|min:0',
        ]);

        // Map Request -> DTO
        $data = new InventoryItemData(
            id: null,
            sku: $request->sku,
            name: $request->name,
            description: $request->description,
            cost: $request->cost ?? 0,
            price: $request->price ?? 0,
            type: 'product',
            category_id: $request->category_id,
            uom_id: $request->uom_id,
            is_active: true
        );

        $action->execute($data);

        return to_route('inventory.dashboard')->with('success', 'Item created successfully.');
    }
}
