<?php

namespace TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\Vendor;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\Customer;

class StockTransfer extends Model
{
    protected $guarded = [];

    // เพิ่ม append attribute เพื่อให้ Inertia ส่ง contact ไปที่ Frontend อัตโนมัติเมื่อมีการเรียก model
    protected $appends = ['contact'];

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

    // ✅ 1. เพิ่ม Relationship Vendor
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'contact_id');
    }

    // ✅ 2. เพิ่ม Relationship Customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'contact_id');
    }

    // ✅ 3. สร้าง Accessor สำหรับรวมร่างเป็น 'contact'
    // Logic: ดูจาก type ว่าควรดึง Vendor หรือ Customer
    public function getContactAttribute()
    {
        if ($this->type === 'incoming') {
            return $this->vendor;
        } elseif (in_array($this->type, ['outgoing', 'picking', 'packing'])) {
            return $this->customer;
        }
        return null;
    }
}
