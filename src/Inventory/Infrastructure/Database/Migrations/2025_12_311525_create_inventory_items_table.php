<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('sku')->unique()->index();
            $table->string('name');
            $table->text('description')->nullable();

            // Normalization: เชื่อมกับตาราง Reference
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->foreignId('uom_id')->constrained('inventory_uoms'); // หน่วยนับหลัก

            $table->string('type')->default('product'); // 'product', 'service', 'consumable'

            // Valuation
            $table->decimal('cost', 15, 4)->default(0);
            $table->decimal('price', 15, 4)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
