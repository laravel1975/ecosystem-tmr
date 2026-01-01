<?php

namespace TmrEcosystem\Inventory\Application\Actions;

use Illuminate\Support\Facades\DB;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;
use Exception;

class ProcessStockMoveAction
{
    public function __construct(
        protected UpdateStockQuantAction $updateStockQuant
    ) {}

    /**
     * ทำการ Post รายการลงบัญชี (เปลี่ยนสถานะเป็น Done และตัดสต็อกจริง)
     */
    public function execute(StockMove $move): StockMove
    {
        // ถ้าสถานะเป็น done แล้ว ห้ามทำซ้ำ
        if ($move->state === 'done') {
            return $move;
        }

        return DB::transaction(function () use ($move) {
            // 1. ตัดยอดออกจากต้นทาง (Source Location) -> ติดลบ (-)
            $this->updateStockQuant->execute(
                $move->item_id,
                $move->source_location_id,
                -1 * abs($move->quantity_done) // ลบออก
            );

            // 2. เพิ่มยอดเข้าปลายทาง (Destination Location) -> บวกเพิ่ม (+)
            $this->updateStockQuant->execute(
                $move->item_id,
                $move->destination_location_id,
                abs($move->quantity_done) // บวกเข้า
            );

            // 3. อัปเดตสถานะของ Move เป็น 'done'
            $move->state = 'done';
            $move->date_done = now();
            $move->save();

            return $move;
        });
    }
}
