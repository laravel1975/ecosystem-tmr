<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique()->nullable();

            // Usage: แยกประเภทให้ชัดเจนเพื่อ query ง่าย
            // 'view' (Folder), 'internal' (คลังจริง), 'customer', 'supplier', 'inventory_loss', 'production'
            $table->string('usage')->default('internal')->index();

            // Hierarchy
            $table->foreignId('parent_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->string('parent_path')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_scrap')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};
