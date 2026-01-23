<?php

namespace TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    protected $table = 'sales_price_lists';

    protected $fillable = ['name', 'code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pricePoints(): HasMany
    {
        return $this->hasMany(PricePoint::class, 'price_list_id');
    }
}
