<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Positions
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('level')->default(1)->comment('1=Staff, 9=Manager');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Employees
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Foreign Keys
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();

            // Placeholder for Inventory Location (จะทำ Index จริงๆ ตอนสร้าง Inventory Domain)
            $table->unsignedBigInteger('inventory_location_id')->nullable()->comment('Link to inventory_locations');

            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
