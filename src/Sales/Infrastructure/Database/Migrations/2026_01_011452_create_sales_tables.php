<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ตารางลูกค้า (Customers)
        Schema::create('sales_customers', function (Blueprint $table) {
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

        // 2. ตารางใบสั่งขาย (Header)
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique(); // e.g., SO-2024-0001

            $table->foreignId('customer_id')->constrained('sales_customers');

            // Status: draft (เสนอราคา), confirmed (รอส่ง), done (ส่งครบ), cancelled
            $table->string('status')->default('draft')->index();

            $table->date('date_order');
            $table->date('date_delivery_expected')->nullable(); // วันที่คาดว่าจะส่ง

            $table->text('notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->timestamps();
        });

        // 3. ตารางรายการสินค้าในใบสั่งขาย (Lines)
        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('sales_orders')->cascadeOnDelete();

            $table->foreignId('item_id')->constrained('inventory_items');
            $table->foreignId('uom_id')->constrained('inventory_uoms');

            $table->decimal('quantity', 15, 4);
            $table->decimal('price_unit', 15, 4)->default(0); // ราคาขาย
            $table->decimal('subtotal', 15, 4)->default(0);

            // เก็บสถานะการส่งของ (Shipped Qty)
            $table->decimal('qty_delivered', 15, 4)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_lines');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('sales_customers');
    }
};
