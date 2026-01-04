<?php

namespace TmrEcosystem\HRM\Application\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class DepartmentData extends Data
{
    public function __construct(
        public string $name,
        public string|Optional $code,
        public string|Optional $description
    ) {}
}
