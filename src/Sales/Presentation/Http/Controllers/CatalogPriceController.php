<?php

namespace TmrEcosystem\Sales\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use TmrEcosystem\Sales\Application\Actions\UpsertPricePointAction;
use TmrEcosystem\Sales\Application\DTOs\PricePointData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\PriceList;

class CatalogPriceController extends Controller
{
    public function index()
    {
        return Inertia::render('Sales/Catalog/Index', [
            'items' => InventoryItem::with(['images', 'pricePoints'])->get(),
            'priceLists' => PriceList::where('is_active', true)->get(),
        ]);
    }

    public function updatePrice(PricePointData $data)
    {
        // $this->authorize('manage price points');

        \TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\PricePoint::updateOrCreate(
            ['inventory_item_id' => $data->inventory_item_id, 'price_list_id' => $data->price_list_id],
            $data->toArray()
        );

        return back()->with('success', 'อัปเดตราคาเรียบร้อยแล้ว');
    }
}
