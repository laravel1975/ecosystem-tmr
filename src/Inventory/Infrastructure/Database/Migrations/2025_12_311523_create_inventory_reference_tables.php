<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1.1 Categories (หมวดหมู่สินค้า) - รองรับหมวดหมู่ย่อย (Hierarchy)
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable(); // e.g., ELEC, FURN
            // Recursive Relation: หมวดหมู่ย่อย -> หมวดหมู่หลัก
            $table->foreignId('parent_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->string('parent_path')->nullable(); // Materialized Path for performance e.g. "1/5/8"
            $table->timestamps();
        });

        // 1.2 Units of Measure (หน่วยนับ) - Normalize จากเดิมที่เป็น String 'pcs'
        Schema::create('inventory_uoms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Pieces, Kilograms, Box of 12
            $table->string('symbol')->nullable(); // e.g., pcs, kg, box
            // Type: 'reference' (หน่วยหลัก), 'bigger' (ใหญ่กว่าหลัก), 'smaller' (เล็กกว่าหลัก)
            $table->string('type')->default('reference');
            $table->decimal('ratio', 10, 5)->default(1.0); // อัตราส่วนเทียบกับหน่วยหลัก
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_categories');
        Schema::dropIfExists('inventory_uoms');
    }
};
