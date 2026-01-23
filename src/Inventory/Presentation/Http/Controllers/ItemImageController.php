<?php

namespace TmrEcosystem\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use TmrEcosystem\Inventory\Application\Actions\ManageItemImagesAction;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItemImage;

class ItemImageController extends Controller
{
    /**
     * บันทึกรูปภาพใหม่
     */
    public function store(Request $request, InventoryItem $item, ManageItemImagesAction $action)
    {
        // ตรวจสอบสิทธิ์ผ่าน Spatie Permission (ถ้ามีการตั้งค่าไว้)
        // $this->authorize('manage inventory items');

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB Limit
            'is_main' => 'boolean'
        ]);

        try {
            $action->upload(
                $item->id,
                $request->file('image'),
                $request->boolean('is_main')
            );

            return back()->with('success', 'อัปโหลดรูปภาพสินค้าเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return back()->withErrors(['image' => $e->getMessage()]);
        }
    }

    /**
     * ตั้งค่ารูปภาพหลัก (Thumbnail)
     */
    public function setMain(InventoryItemImage $image)
    {
        // ยกเลิกรูปหลักเดิมของสินค้านั้นๆ
        InventoryItemImage::where('inventory_item_id', $image->inventory_item_id)
            ->update(['is_main' => false]);

        // ตั้งรูปนี้เป็นรูปหลัก
        $image->update(['is_main' => true]);

        return back()->with('success', 'ตั้งเป็นรูปหลักเรียบร้อยแล้ว');
    }

    /**
     * ลบรูปภาพ
     */
    public function destroy(InventoryItemImage $image)
    {
        // ลบไฟล์จริงใน Storage
        if (Storage::disk('public')->exists($image->file_path)) {
            Storage::disk('public')->delete($image->file_path);
        }

        $itemId = $image->inventory_item_id;
        $wasMain = $image->is_main;

        $image->delete();

        // ถ้าลบรูปหลักไป ให้สุ่มตั้งรูปที่เหลือเป็นรูปหลักแทน (ถ้ามี)
        if ($wasMain) {
            $nextImage = InventoryItemImage::where('inventory_item_id', $itemId)->first();
            if ($nextImage) {
                $nextImage->update(['is_main' => true]);
            }
        }

        return back()->with('success', 'ลบรูปภาพเรียบร้อยแล้ว');
    }
}
