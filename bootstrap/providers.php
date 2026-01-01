<?php

use TmrEcosystem\Inventory\Infrastructure\Providers\InventoryServiceProvider;
use TmrEcosystem\Purchase\Infrastructure\Providers\PurchaseServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    InventoryServiceProvider::class,
    PurchaseServiceProvider::class,
];
