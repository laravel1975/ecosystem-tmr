<?php

namespace TmrEcosystem\HRM\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';

    protected $fillable = ['name', 'description', 'level'];
}
