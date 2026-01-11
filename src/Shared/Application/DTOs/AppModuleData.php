<?php

namespace TmrEcosystem\Shared\Application\DTOs; // ต้องขึ้นต้นด้วย TmrEcosystem

use Spatie\LaravelData\Data;

class AppModuleData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $icon,
        public string $route,
        public string $color,
        public int $badge_count = 0,
        public bool $is_active = true
    ) {}
}
