<?php

namespace TmrEcosystem\Inventory\Application\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItemImage;

class ManageItemImagesAction
{
    public function upload(int $itemId, UploadedFile $file, bool $isMain = false): InventoryItemImage
    {
        $currentCount = InventoryItemImage::where('inventory_item_id', $itemId)->count();

        if ($currentCount >= 5) {
            throw new \Exception("สินค้าหนึ่งชิ้นมีรูปภาพได้ไม่เกิน 5 รูป");
        }

        // ถ้าตั้งเป็นรูปหลัก หรือเป็นรูปแรก ให้เคลียร์รูปหลักเดิม
        if ($isMain || $currentCount === 0) {
            InventoryItemImage::where('inventory_item_id', $itemId)->update(['is_main' => false]);
            $isMain = true;
        }

        $path = $file->store("catalog/items/{$itemId}", 'public');

        return InventoryItemImage::create([
            'inventory_item_id' => $itemId,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'is_main' => $isMain,
            'sort_order' => $currentCount + 1
        ]);
    }
}
