<?php

namespace TmrEcosystem\Inventory\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interfaces here if needed
    }

    public function boot(): void
    {
        // ✅ 1. Load Migrations จาก Folder ใน src/
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // ✅ 2. Load Routes (ถ้ามี)
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Http/routes/inventory.php');
    }
}
