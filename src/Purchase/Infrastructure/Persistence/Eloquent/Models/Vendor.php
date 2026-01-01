<?php

namespace TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'purchasing_vendors';
    protected $guarded = [];
}
