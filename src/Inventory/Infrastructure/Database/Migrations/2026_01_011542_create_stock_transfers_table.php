<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // เลขที่เอกสาร: IN26-00001, OUT26-00001
            $table->string('reference')->unique();

            // ประเภทเอกสาร: incoming, outgoing, internal, picking, packing
            $table->string('type')->index();

            $table->foreignId('source_location_id')->constrained('inventory_locations');
            $table->foreignId('destination_location_id')->constrained('inventory_locations');

            // Contact (Optional): เก็บ ID ของ Supplier หรือ Customer
            $table->unsignedBigInteger('contact_id')->nullable();

            $table->string('status')->default('draft'); // draft, ready, done, cancelled
            $table->text('note')->nullable();
            $table->date('scheduled_date')->nullable();

            $table->timestamps();
        });

        // เชื่อม stock_moves เข้ากับ transfer
        Schema::table('stock_moves', function (Blueprint $table) {
            $table->foreignId('transfer_id')->nullable()->constrained('stock_transfers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_moves', function (Blueprint $table) {
            $table->dropForeign(['transfer_id']);
            $table->dropColumn('transfer_id');
        });
        Schema::dropIfExists('stock_transfers');
    }
};
