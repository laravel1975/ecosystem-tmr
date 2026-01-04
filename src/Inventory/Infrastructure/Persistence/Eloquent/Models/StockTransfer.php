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

    // ✅ Appends 'contact' เพื่อให้ Frontend (Inertia) เห็นข้อมูลนี้โดยอัตโนมัติ
    protected $appends = ['contact'];

    // --------------------------------------------------------------------------
    // Relationships พื้นฐาน
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
    // Relationships สำหรับ Vendor/Customer
    // --------------------------------------------------------------------------

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'contact_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'contact_id');
    }

    // ✅ Accessor: รวมร่าง Vendor/Customer ให้เป็น 'contact' object เดียว
    // เพื่อให้เรียกใช้ได้ง่ายๆ เช่น $transfer->contact->name
    public function getContactAttribute()
    {
        if ($this->type === 'incoming') {
            return $this->vendor;
        }

        // Picking, Packing, Outgoing ใช้ Customer
        if (in_array($this->type, ['outgoing', 'picking', 'packing'])) {
            return $this->customer;
        }

        return null;
    }

    // --------------------------------------------------------------------------
    // Relationships Chain & Backorder
    // --------------------------------------------------------------------------

    public function previousTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'previous_transfer_id');
    }

    public function nextTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'previous_transfer_id');
    }

    public function originTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'origin_transfer_id');
    }
}
