<?php

namespace TmrEcosystem\Inventory\Application\Services;

use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockTransfer;
use Illuminate\Support\Str;

class DocumentNumberService
{
    public function generateNextNumber(string $type): string
    {
        // 1. กำหนด Prefix ตาม Type
        $prefixCode = match ($type) {
            'incoming' => 'IN',
            'outgoing' => 'OUT',
            'internal' => 'INT',
            'picking'  => 'PICK',
            'packing'  => 'PACK',
            default    => 'DOC',
        };

        // 2. ดึงเลขปี 2 หลัก (เช่น 26)
        $year = date('y');

        // Prefix เต็มรูปแบบ: IN26-
        $fullPrefix = $prefixCode . $year . '-';

        // 3. หาเลขล่าสุดใน Database ที่ขึ้นต้นด้วย Prefix นี้
        $lastRecord = StockTransfer::where('reference', 'like', $fullPrefix . '%')
            ->orderByRaw('LENGTH(reference) DESC') // เรียงตามความยาวก่อน
            ->orderBy('reference', 'desc')         // เรียงตามตัวอักษร
            ->first();

        $nextNumber = 1;

        if ($lastRecord) {
            // ตัด Prefix ออก เหลือแค่เลข แล้วบวก 1
            // IN26-00001 -> 00001 -> 1
            $lastRef = $lastRecord->reference;
            $numberPart = (int) Str::after($lastRef, $fullPrefix);
            $nextNumber = $numberPart + 1;
        }

        // 4. Return Format: IN26-00001 (Padding 5 หลัก)
        return $fullPrefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
