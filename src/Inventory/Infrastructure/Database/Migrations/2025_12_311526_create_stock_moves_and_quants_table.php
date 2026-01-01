<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 4.1 Stock Moves (สมุดบัญชี - Journal Entries)
        Schema::create('stock_moves', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relation
            $table->foreignId('item_id')->constrained('inventory_items');
            $table->foreignId('uom_id')->constrained('inventory_uoms'); // หน่วยตอนที่เคลื่อนย้าย

            // Double-Entry Core
            $table->foreignId('source_location_id')->constrained('inventory_locations');
            $table->foreignId('destination_location_id')->constrained('inventory_locations');

            // Quantities
            $table->decimal('quantity_demand', 15, 4)->default(0);
            $table->decimal('quantity_done', 15, 4)->default(0);

            // Status & Tracking
            $table->string('state')->default('draft')->index(); // draft, confirmed, assigned, done, cancelled
            $table->string('batch_number')->nullable()->index(); // รองรับ Lot/Serial (ถ้าจะ Advance ให้แยกตาราง stock_lots)

            // Polymorphic Relation: ต้นเรื่องมาจากไหน (SO, PO, MO)
            $table->nullableMorphs('reference');

            $table->timestamp('date_expected')->nullable();
            $table->timestamp('date_done')->nullable();
            $table->timestamps();
        });

        // 4.2 Stock Quants (ยอดคงเหลือปัจจุบัน - Snapshot)
        // ตารางนี้จริงๆ คือ Denormalized (คำนวณจาก Moves ได้) แต่ต้องมีเพื่อ Performance
        Schema::create('stock_quants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items');
            $table->foreignId('location_id')->constrained('inventory_locations');

            // เก็บ Lot/Batch ด้วยถ้ามี (เพื่อให้รู้ว่า Lot นี้เหลือที่ไหนเท่าไหร่)
            $table->string('batch_number')->nullable()->index();

            $table->decimal('quantity', 15, 4)->default(0); // ยอดจริง (On Hand)
            $table->decimal('reserved_quantity', 15, 4)->default(0); // ยอดที่ถูกจอง (Reserved)

            $table->timestamps();

            // Constraint: 1 สินค้า + 1 สถานที่ + 1 ล็อต ต้องมีแถวเดียว
            $table->unique(['item_id', 'location_id', 'batch_number'], 'quant_unique_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_moves');
        Schema::dropIfExists('stock_quants');
    }
};
