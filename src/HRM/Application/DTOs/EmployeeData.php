<?php

namespace TmrEcosystem\HRM\Application\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class EmployeeData extends Data
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $code,
        public string|Optional $email,
        public string|Optional $phone,
        public ?int $user_id,
        public ?int $department_id,
        public ?int $position_id,
        public ?int $inventory_location_id,
    ) {}
}
