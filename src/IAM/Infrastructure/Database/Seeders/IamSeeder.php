<?php

namespace TmrEcosystem\IAM\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class IamSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions (Granular)
        $permissions = [
            // Inventory
            'inventory.view', 'inventory.create_item', 'inventory.move_stock', 'inventory.adjust_stock',
            // Sales
            'sales.view', 'sales.create_order', 'sales.approve_order',
            // Purchase
            'purchase.view', 'purchase.create_order', 'purchase.approve_order',
            // HRM
            'hrm.view_employees', 'hrm.manage_employees',
            // System
            'system.manage_users', 'system.view_audit_logs'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Define Roles & Assign Permissions

        // --- Admin ---
        $admin = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super Admin gets all permissions via Gate::before rule usually,
        // but we can also assign all.

        // --- Inventory Manager ---
        $invManager = Role::firstOrCreate(['name' => 'Inventory Manager']);
        $invManager->syncPermissions([
            'inventory.view', 'inventory.create_item', 'inventory.move_stock', 'inventory.adjust_stock'
        ]);

        // --- Sales Representative ---
        $salesRep = Role::firstOrCreate(['name' => 'Sales Representative']);
        $salesRep->syncPermissions([
            'sales.view', 'sales.create_order', 'inventory.view' // Sales need to see stock
        ]);

        // --- Purchasing Officer ---
        $purchaser = Role::firstOrCreate(['name' => 'Purchasing Officer']);
        $purchaser->syncPermissions([
            'purchase.view', 'purchase.create_order', 'inventory.view'
        ]);

        // --- HR Manager ---
        $hrManager = Role::firstOrCreate(['name' => 'HR Manager']);
        $hrManager->syncPermissions([
            'hrm.view_employees', 'hrm.manage_employees'
        ]);
    }
}
