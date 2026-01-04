<?php

namespace TmrEcosystem\IAM\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class IAMServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
