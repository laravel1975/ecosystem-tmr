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

    public function gallery(Request $request)
    {
        // ดึงข้อมูลสินค้าพร้อมรูปหลัก และราคา (กรองเอาเฉพาะที่มีรูปและ Active)
        $items = InventoryItem::with(['mainImage', 'images', 'pricePoints.priceList'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->get();

        return Inertia::render('Sales/Catalog/Gallery', [
            'items' => $items,
            'filters' => $request->only(['search'])
        ]);
    }
}
