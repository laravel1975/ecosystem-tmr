<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ✅ เพิ่มบรรทัดนี้

return new class extends Migration
{
    public function up(): void
    {
        // 1. เพิ่ม Source Document ในตาราง Stock Transfers
        Schema::table('stock_transfers', function (Blueprint $table) {
            // เช็คก่อนสร้าง เพื่อป้องกัน error ถ้าคอลัมน์มีอยู่แล้ว
            if (!Schema::hasColumn('stock_transfers', 'source_document')) {
                $table->string('source_document')->nullable()->after('reference')->index();
            }
        });

        // 2. ปรับปรุงสถานะ Sales Orders (เพิ่ม Index)
        // ใช้ SQL เช็ค Index แทน Doctrine เพื่อความชัวร์และรองรับ Laravel ทุกเวอร์ชัน
        $indexName = 'sales_orders_status_index';
        $indexExists = false;

        try {
            // ดึงรายชื่อ Index ของตาราง sales_orders มาตรวจสอบ
            $indexes = DB::select("SHOW INDEXES FROM sales_orders");
            foreach ($indexes as $index) {
                if ($index->Key_name === $indexName) {
                    $indexExists = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            // กรณี Table ไม่เจอ หรือ Error อื่นๆ ให้ข้ามไป (ปกติไม่ควรเกิด)
        }

        if (!$indexExists) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->index('status', 'sales_orders_status_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transfers', 'source_document')) {
                $table->dropColumn('source_document');
            }
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            // เช็คก่อนลบ
            try {
                $table->dropIndex('sales_orders_status_index');
            } catch (\Exception $e) {
                // Index might not exist
            }
        });
    }
};
