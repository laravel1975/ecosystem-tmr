<?php

namespace TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\Vendor;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\Customer;

class StockTransfer extends Model
{
    // ใช้ guarded = [] เพื่ออนุญาตให้เติมข้อมูลได้ทุก field (รวมถึง source_document)
    protected $guarded = [];

    // เพิ่ม append attribute เพื่อให้ Inertia ส่ง contact ไปที่ Frontend อัตโนมัติเมื่อมีการเรียก model
    protected $appends = ['contact'];

    // --------------------------------------------------------------------------
    // Relationships พื้นฐาน (Moves, Location)
    // --------------------------------------------------------------------------

    public function moves(): HasMany
    {
        return $this->hasMany(StockMove::class, 'transfer_id');
    }

    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'source_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'destination_location_id');
    }

    // --------------------------------------------------------------------------
    // Relationships สำหรับ Vendor/Customer (Contact)
    // --------------------------------------------------------------------------

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'contact_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'contact_id');
    }

    // Accessor สำหรับรวมร่างเป็น 'contact'
    public function getContactAttribute()
    {
        if ($this->type === 'incoming') {
            return $this->vendor;
        } elseif (in_array($this->type, ['outgoing', 'picking', 'packing'])) {
            return $this->customer;
        }
        return null;
    }

    // --------------------------------------------------------------------------
    // 🔥 Relationships ใหม่สำหรับ Chain Operation & Backorder
    // --------------------------------------------------------------------------

    /**
     * ใบก่อนหน้า (Parent) ที่เรากำลังรออยู่
     * เช่น ใบ Packing (Waiting) จะมี previousTransfer เป็นใบ Picking
     */
    public function previousTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'previous_transfer_id');
    }

    /**
     * ใบถัดไป (Children) ที่กำลังรอเราอยู่
     * เช่น ใบ Picking (Ready) จะมี nextTransfers เป็นใบ Packing (Waiting)
     */
    public function nextTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'previous_transfer_id');
    }

    /**
     * ใบต้นทางสุด (Root) ของกระบวนการ (Optional)
     * ใช้สำหรับ Track กลับไปหาจุดเริ่มต้นของ Chain นี้
     */
    public function originTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'origin_transfer_id');
    }
}
