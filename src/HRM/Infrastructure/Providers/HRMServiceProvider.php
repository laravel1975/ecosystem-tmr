<?php

namespace TmrEcosystem\HRM\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class HRMServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Http/routes/hrm.php');
    }
}
