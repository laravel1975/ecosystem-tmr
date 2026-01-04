<?php

namespace TmrEcosystem\IAM\Infrastructure\Persistence\Eloquent\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Override เพื่อกำหนด connection หรือ logic เพิ่มเติมในอนาคต
    // protected $connection = 'mysql';
}
