<?php

namespace TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'sales_customers';
    protected $guarded = [];
}
