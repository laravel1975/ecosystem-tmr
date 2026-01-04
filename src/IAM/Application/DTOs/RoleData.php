<?php

namespace TmrEcosystem\IAM\Application\DTOs;

use Spatie\LaravelData\Data;

class RoleData extends Data
{
    public function __construct(
        public string $name,
        public array $permissions = []
    ) {}
}
