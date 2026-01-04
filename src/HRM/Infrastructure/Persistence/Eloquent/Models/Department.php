<?php

namespace TmrEcosystem\HRM\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = ['name', 'code', 'description'];
}
