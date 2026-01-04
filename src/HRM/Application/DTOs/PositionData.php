<?php

namespace TmrEcosystem\HRM\Application\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class PositionData extends Data
{
    public function __construct(
        public string $name,
        public int|Optional $level,
        public string|Optional $description
    ) {}
}
