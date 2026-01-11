<?php

namespace TmrEcosystem\IAM\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class IamRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset Cached Roles/Permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Define Granular Permissions (การกระทำเล็กๆ)
        $permissions = [
            // --- Sales ---
            'sales.view_own',       // ดูเฉพาะของตัวเอง
            'sales.view_all',       // ดูได้ทั้งหมด
            'sales.manage',         // สร้าง/แก้ไข
            'sales.config',         // ตั้งค่าระบบขาย (Admin)

            // --- Purchase ---
            'purchase.view_own',
            'purchase.view_all',
            'purchase.manage',
            'purchase.approve',     // อนุมัติ PO
            'purchase.config',      // ตั้งค่าระบบซื้อ (Admin)

            // --- Inventory ---
            'inventory.view',
            'inventory.move',       // ย้ายของ
            'inventory.adjust',     // ปรับยอด (นับสต็อก)
            'inventory.config',     // สร้าง Location/Item ใหม่ (Admin)

            // --- Portal (External) ---
            'portal.access',        // เข้าหน้า Portal ได้
            'portal.view_orders',   // ดูรายการสั่งซื้อตัวเอง
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 3. Create Roles & Assign Permissions (จับคู่ตาม Requirement)

        // --- Sales Roles ---
        Role::firstOrCreate(['name' => 'Sales: Own Documents'])
            ->syncPermissions(['sales.view_own', 'sales.manage']);

        Role::firstOrCreate(['name' => 'Sales: All Documents'])
            ->syncPermissions(['sales.view_own', 'sales.view_all', 'sales.manage']);

        Role::firstOrCreate(['name' => 'Sales: Administrator'])
            ->syncPermissions(['sales.view_own', 'sales.view_all', 'sales.manage', 'sales.config']);

        // --- Purchase Roles ---
        Role::firstOrCreate(['name' => 'Purchase: User'])
            ->syncPermissions(['purchase.view_own', 'purchase.manage']);

        Role::firstOrCreate(['name' => 'Purchase: Administrator'])
            ->syncPermissions(['purchase.view_all', 'purchase.manage', 'purchase.approve', 'purchase.config']);

        // --- Inventory Roles ---
        Role::firstOrCreate(['name' => 'Inventory: User'])
            ->syncPermissions(['inventory.view', 'inventory.move']);

        Role::firstOrCreate(['name' => 'Inventory: Administrator'])
            ->syncPermissions(['inventory.view', 'inventory.move', 'inventory.adjust', 'inventory.config']);

        // --- Portal Roles (External) ---
        Role::firstOrCreate(['name' => 'Portal: Customer'])
            ->syncPermissions(['portal.access', 'portal.view_orders']);

        Role::firstOrCreate(['name' => 'Portal: Vendor'])
            ->syncPermissions(['portal.access']); // อาจจะดู PO ที่ส่งมาหาตัวเอง
    }
}
