<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Business Roles (ใช้ Filter ใน Dropdown เช่น เลือกพนักงานขาย)
            $table->boolean('is_salesperson')->default(false)->after('position_id');
            $table->boolean('is_purchaser')->default(false)->after('is_salesperson');
            $table->boolean('is_technician')->default(false)->after('is_purchaser');

            // Inventory Context
            // พนักงานคนนี้ดูแลคลังไหนเป็นหลัก? (Default Warehouse)
            $table->unsignedBigInteger('default_warehouse_id')->nullable()->after('is_technician');

            // พนักงานคนนี้ "เป็น" Location หรือไม่? (เช่น รถ Service)
            // ถ้ามีค่านี้ แสดงว่าพนักงานถือของได้ (Mobile Inventory)
            // *หมายเหตุ: inventory_location_id เรามีแล้วใน Migration เดิม ถ้ายังไม่มีให้ Uncomment บรรทัดล่าง*
            // $table->unsignedBigInteger('inventory_location_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['is_salesperson', 'is_purchaser', 'is_technician', 'default_warehouse_id']);
        });
    }
};
