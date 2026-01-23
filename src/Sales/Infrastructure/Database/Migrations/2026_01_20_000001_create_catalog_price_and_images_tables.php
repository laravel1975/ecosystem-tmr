<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ตารางกลุ่มราคา (Price Lists)
        Schema::create('sales_price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // เช่น ราคาขายส่ง, ราคา Campaign A
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. ตารางจุดราคา (Price Points)
        Schema::create('sales_price_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('price_list_id')->constrained('sales_price_lists')->cascadeOnDelete();
            $table->decimal('amount', 15, 4);
            $table->string('currency')->default('THB');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->timestamps();

            $table->unique(['inventory_item_id', 'price_list_id'], 'idx_item_price_list');
        });

        // 3. ตารางรูปภาพสินค้า (Max 5 images + Thumbnail)
        Schema::create('inventory_item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->boolean('is_main')->default(false); // สำหรับ Thumbnail
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item_images');
        Schema::dropIfExists('sales_price_points');
        Schema::dropIfExists('sales_price_lists');
    }
};
