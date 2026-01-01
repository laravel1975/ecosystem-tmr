<?php

namespace TmrEcosystem\Inventory\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;
// *หมายเหตุ: โปรดปรับ Namespace Model ให้ตรงกับที่คุณสร้างจริง

class InventoryLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. สร้าง Virtual Locations (สถานที่เสมือน)
        // ใช้สำหรับ: การปรับยอด (Loss), การผลิต, หรือ Scrapped
        $virtualRoot = $this->createLocation([
            'name' => 'Virtual Locations',
            'code' => 'VIRTUAL',
            'usage' => 'view', // เป็นแค่ Folder เก็บ
        ]);

        $this->createLocation([
            'name' => 'Inventory Adjustment',
            'code' => 'INV-ADJ',
            'usage' => 'inventory', // ใช้ปรับยอด (Stock +/-)
            'parent_id' => $virtualRoot->id,
        ]);

        $this->createLocation([
            'name' => 'Scrapped',
            'code' => 'SCRAP',
            'usage' => 'inventory', // ของเสีย
            'is_scrap' => true,
            'parent_id' => $virtualRoot->id,
        ]);

        $this->createLocation([
            'name' => 'Production',
            'code' => 'PROD',
            'usage' => 'production', // ใช้ในการผลิต (Raw Mat -> Production -> Finish Good)
            'parent_id' => $virtualRoot->id,
        ]);

        // 2. สร้าง Partner Locations (สถานที่ภายนอก)
        // ใช้สำหรับ: ซื้อของเข้า (Vendor) และ ขายของออก (Customer)
        $partnerRoot = $this->createLocation([
            'name' => 'Partner Locations',
            'code' => 'PARTNER',
            'usage' => 'view',
        ]);

        $this->createLocation([
            'name' => 'Vendors',
            'code' => 'VENDORS',
            'usage' => 'supplier', // แหล่งที่มาของการซื้อ (Purchase)
            'parent_id' => $partnerRoot->id,
        ]);

        $this->createLocation([
            'name' => 'Customers',
            'code' => 'CUSTOMERS',
            'usage' => 'customer', // ปลายทางของการขาย (Sales)
            'parent_id' => $partnerRoot->id,
        ]);

        // 3. สร้าง Physical Warehouse (คลังสินค้าจริง)
        // โครงสร้างมาตรฐาน: Warehouse -> Stock
        $wh = $this->createLocation([
            'name' => 'Main Warehouse',
            'code' => 'WH',
            'usage' => 'view', // ตัวคลังหลักมักเป็น View เพื่อดูยอดรวมลูกๆ
        ]);

        // 3.1 พื้นที่เก็บสินค้าหลัก (General Stock)
        $stock = $this->createLocation([
            'name' => 'Stock',
            'code' => 'WH-STOCK',
            'usage' => 'internal', // เก็บของจริง
            'parent_id' => $wh->id,
        ]);

        // 3.2 (Optional) พื้นที่รับของ (Input) และส่งของ (Output) สำหรับกระบวนการ 2-step
        $this->createLocation([
            'name' => 'Input/Receiving',
            'code' => 'WH-IN',
            'usage' => 'internal',
            'parent_id' => $wh->id,
        ]);

        $this->createLocation([
            'name' => 'Output/Dispatch',
            'code' => 'WH-OUT',
            'usage' => 'internal',
            'parent_id' => $wh->id,
        ]);

        // 3.3 ตัวอย่างชั้นวางสินค้า (Shelf/Bin)
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
        // เช่น "1/5/8" เพื่อให้ Query ลูกหลานได้เร็วๆ
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
