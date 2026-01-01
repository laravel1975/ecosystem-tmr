<?php

namespace TmrEcosystem\Sales\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. โหลด Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // 2. โหลด Routes (เดี๋ยวเราค่อยสร้างไฟล์นี้ในขั้นตอนถัดไป)
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/routes/sales.php');
    }
}
