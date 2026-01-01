<?php

namespace TmrEcosystem\Inventory\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryCategory;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryUom;

class InventoryMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. สร้าง Units of Measure (UOM)
        $uomPcs = InventoryUom::firstOrCreate(
            ['name' => 'Pieces'],
            ['symbol' => 'pcs', 'type' => 'reference', 'ratio' => 1.0]
        );

        $uomKg = InventoryUom::firstOrCreate(
            ['name' => 'Kilogram'],
            ['symbol' => 'kg', 'type' => 'reference', 'ratio' => 1.0]
        );

        // 2. สร้าง Categories
        $catElectronics = InventoryCategory::firstOrCreate(
            ['code' => 'ELEC'],
            ['name' => 'Electronics']
        );

        // 3. สร้าง Items ตัวอย่าง
        InventoryItem::firstOrCreate(
            ['sku' => 'TEST-001'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Test Product A',
                'description' => 'สินค้าทดสอบ A',
                'category_id' => $catElectronics->id,
                'uom_id' => $uomPcs->id,
                'type' => 'product',
                'cost' => 100,
                'price' => 250,
                'is_active' => true,
            ]
        );

        InventoryItem::firstOrCreate(
            ['sku' => 'RAW-001'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Raw Material X',
                'category_id' => $catElectronics->id,
                'uom_id' => $uomKg->id,
                'type' => 'product',
                'cost' => 50,
                'price' => 0, // ไม่ขาย
            ]
        );
    }
}
