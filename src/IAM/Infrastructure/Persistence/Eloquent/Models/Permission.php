<?php

namespace TmrEcosystem\IAM\Infrastructure\Persistence\Eloquent\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // Override เพื่อกำหนด connection หรือ logic เพิ่มเติมในอนาคต
}
