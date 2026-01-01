<?php

namespace TmrEcosystem\Purchase\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class PurchaseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // โหลด Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // โหลด Routes (เดี๋ยวเราจะมาสร้างไฟล์นี้กัน)
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Http/routes/purchase.php');
    }
}
