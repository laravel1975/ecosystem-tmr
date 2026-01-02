<?php

namespace TmrEcosystem\Inventory\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;

class InventoryLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. สร้าง Virtual Locations (สถานที่เสมือน)
        $virtualRoot = $this->createLocation([
            'name' => 'Virtual Locations',
            'code' => 'VIRTUAL',
            'usage' => 'view',
        ]);

        $this->createLocation([
            'name' => 'Inventory Adjustment',
            'code' => 'INV-ADJ',
            'usage' => 'inventory',
            'parent_id' => $virtualRoot->id,
        ]);

        $this->createLocation([
            'name' => 'Scrapped',
            'code' => 'SCRAP',
            'usage' => 'inventory',
            'is_scrap' => true,
            'parent_id' => $virtualRoot->id,
        ]);

        $this->createLocation([
            'name' => 'Production',
            'code' => 'PROD',
            'usage' => 'production',
            'parent_id' => $virtualRoot->id,
        ]);

        // 2. สร้าง Partner Locations (สถานที่ภายนอก)
        $partnerRoot = $this->createLocation([
            'name' => 'Partner Locations',
            'code' => 'PARTNER',
            'usage' => 'view',
        ]);

        $this->createLocation([
            'name' => 'Vendors',
            'code' => 'VENDORS',
            'usage' => 'supplier',
            'parent_id' => $partnerRoot->id,
        ]);

        $this->createLocation([
            'name' => 'Customers',
            'code' => 'CUSTOMERS',
            'usage' => 'customer',
            'parent_id' => $partnerRoot->id,
        ]);

        // 3. สร้าง Physical Warehouse (คลังสินค้าจริง)
        $wh = $this->createLocation([
            'name' => 'Main Warehouse',
            'code' => 'WH',
            'usage' => 'view',
        ]);

        // 3.1 พื้นที่เก็บสินค้าหลัก (General Stock)
        $stock = $this->createLocation([
            'name' => 'Stock',
            'code' => 'WH-STOCK',
            'usage' => 'internal',
            'parent_id' => $wh->id,
        ]);

        // 3.2 พื้นที่รับของ (Input)
        $this->createLocation([
            'name' => 'Input/Receiving',
            'code' => 'WH-IN',
            'usage' => 'internal',
            'parent_id' => $wh->id,
        ]);

        // 3.3 ✅ เพิ่ม: พื้นที่แพ็คสินค้า (Packing Zone)
        $this->createLocation([
            'name' => 'Packing Zone', // ชื่อต้องตรงกับ Code ใน Controller
            'code' => 'WH-PACK',
            'usage' => 'internal',
            'parent_id' => $wh->id,
        ]);

        // 3.4 ✅ เพิ่ม: พื้นที่รอส่งสินค้า (Output Zone)
        $this->createLocation([
            'name' => 'Output Zone', // ชื่อต้องตรงกับ Code ใน Controller
            'code' => 'WH-OUT',
            'usage' => 'internal',
            'parent_id' => $wh->id,
        ]);

        // 3.5 ตัวอย่างชั้นวางสินค้า (Shelf/Bin)
        $this->createLocation([
            'name' => 'Shelf A',
            'code' => 'WH-STOCK-A',
            'usage' => 'internal',
            'parent_id' => $stock->id,
        ]);

        $this->createLocation([
            'name' => 'Shelf B',
            'code' => 'WH-STOCK-B',
            'usage' => 'internal',
            'parent_id' => $stock->id,
        ]);
    }

    /**
     * Helper Function เพื่อสร้าง Location และจัดการ Path
     */
    private function createLocation(array $data): InventoryLocation
    {
        // สร้าง Location
        $location = InventoryLocation::firstOrCreate(
            ['code' => $data['code']], // เช็คจาก Code เพื่อไม่ให้ซ้ำ
            array_merge($data, ['uuid' => Str::uuid()])
        );

        // Update Parent Path Logic (Materialized Path)
        if (!empty($data['parent_id'])) {
            $parent = InventoryLocation::find($data['parent_id']);
            if ($parent) {
                $location->parent_path = $parent->parent_path
                    ? $parent->parent_path . '/' . $parent->id
                    : (string)$parent->id;
                $location->save();
            }
        }

        return $location;
    }
}
