<?php

use TmrEcosystem\HRM\Infrastructure\Providers\HRMServiceProvider;
use TmrEcosystem\IAM\Infrastructure\Providers\IAMServiceProvider;
use TmrEcosystem\Inventory\Infrastructure\Providers\InventoryServiceProvider;
use TmrEcosystem\Purchase\Infrastructure\Providers\PurchaseServiceProvider;
use TmrEcosystem\Sales\Infrastructure\Providers\SalesServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    InventoryServiceProvider::class,
    PurchaseServiceProvider::class,
    SalesServiceProvider::class,
    IAMServiceProvider::class,
    HRMServiceProvider::class,
];
