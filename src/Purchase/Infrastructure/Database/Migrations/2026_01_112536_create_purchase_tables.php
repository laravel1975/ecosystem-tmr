<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ตารางคู่ค้า (Vendors)
        Schema::create('purchasing_vendors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('tax_id')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. ตารางใบสั่งซื้อ (Header)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique(); // e.g., PO-2024-0001

            $table->foreignId('vendor_id')->constrained('purchasing_vendors');

            // Status: draft, confirmed (รอของ), received (รับครบแล้ว), cancelled
            $table->string('status')->default('draft')->index();

            $table->date('date_order');
            $table->date('date_expected')->nullable(); // วันที่คาดว่าจะได้รับของ

            $table->text('notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0); // ยอดรวมเงิน (คร่าวๆ)

            $table->timestamps();
        });

        // 3. ตารางรายการสินค้าในใบสั่งซื้อ (Lines)
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('purchase_orders')->cascadeOnDelete();

            // เชื่อมกับ Inventory Item
            $table->foreignId('item_id')->constrained('inventory_items');
            $table->foreignId('uom_id')->constrained('inventory_uoms'); // หน่วยนับตอนซื้อ

            $table->decimal('quantity', 15, 4);
            $table->decimal('price_unit', 15, 4)->default(0); // ราคาต่อหน่วย
            $table->decimal('subtotal', 15, 4)->default(0);

            // เก็บสถานะการรับของรายบรรทัด (เผื่อรับของไม่ครบ - Partial Receipt)
            $table->decimal('qty_received', 15, 4)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchasing_vendors');
    }
};
