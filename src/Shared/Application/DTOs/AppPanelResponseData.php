<?php

namespace TmrEcosystem\Shared\Application\DTOs; // ต้องขึ้นต้นด้วย TmrEcosystem

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class AppPanelResponseData extends Data
{
    public function __construct(
        /** @var DataCollection<AppModuleData> */
        public DataCollection $modules,
        public string $system_message,
        public array $user_summary
    ) {}
}
