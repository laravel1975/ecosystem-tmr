<?php

namespace TmrEcosystem\HRM\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TmrEcosystem\IAM\Infrastructure\Persistence\Eloquent\Models\User;
// use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation; // เปิดใช้เมื่อสร้าง Inventory Module เสร็จ

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'user_id',
        'department_id',
        'position_id',
        'inventory_location_id', // Link ไปยัง Inventory Location (Personal Stock)
        'status',

        // --- ERP Roles & Context ---
        'is_salesperson',       // เป็นพนักงานขายหรือไม่
        'is_purchaser',         // เป็นเจ้าหน้าที่จัดซื้อหรือไม่
        'is_technician',        // เป็นช่างเทคนิคหรือไม่
        'default_warehouse_id', // คลังสินค้าหลักที่ประจำอยู่
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_salesperson' => 'boolean',
        'is_purchaser' => 'boolean',
        'is_technician' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    // Optional: ความสัมพันธ์กับ Warehouse (ถ้ามี Model แล้ว)
    /*
    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'default_warehouse_id');
    }
    */

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
