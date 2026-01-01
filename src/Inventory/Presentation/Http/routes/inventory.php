<?php

use Illuminate\Support\Facades\Route;
use TmrEcosystem\Inventory\Presentation\Http\Controllers\InventoryDashboardController;
use TmrEcosystem\Inventory\Presentation\Http\Controllers\ItemController;
use TmrEcosystem\Inventory\Presentation\Http\Controllers\StockMovementController;
use TmrEcosystem\Inventory\Presentation\Http\Controllers\OperationsController;

/*
|--------------------------------------------------------------------------
| Inventory Routes
|--------------------------------------------------------------------------
|
| รวม Route ทั้งหมดที่เกี่ยวกับ Inventory Domain
|
*/

Route::middleware(['web', 'auth', 'verified']) // กำหนด Middleware ที่จำเป็น
    ->prefix('inventory')                      // กำหนด Prefix URL (เช่น /inventory/dashboard)
    ->name('inventory.')                       // กำหนด Prefix Name (เช่น inventory.dashboard)
    ->group(function () {

        // Dashboard: /inventory
        Route::get('/', InventoryDashboardController::class)->name('dashboard');

        // ตัวอย่าง: ถ้ามี Route อื่นๆ ในอนาคต
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');

        // Stock Operations
        Route::get('/operations/receive', [StockMovementController::class, 'createReceive'])->name('operations.receive');
        Route::post('/operations/receive', [StockMovementController::class, 'storeReceive'])->name('operations.store_receive');

        // 👇 Delivery Routes
        Route::get('/operations/delivery', [StockMovementController::class, 'createDelivery'])->name('operations.delivery');
        Route::post('/operations/delivery', [StockMovementController::class, 'storeDelivery'])->name('operations.store_delivery');

        Route::post('/moves/{id}/validate', [StockMovementController::class, 'validateMove'])->name('moves.validate');

        // Operations Routes (แยก Type: incoming, outgoing, picking, packing, internal)
        Route::get('/ops/{type}', [OperationsController::class, 'index'])->name('ops.index');
        Route::get('/ops/doc/{id}', [OperationsController::class, 'show'])->name('ops.show');
        Route::post('/ops/doc/{id}/validate', [OperationsController::class, 'validateTransfer'])->name('ops.validate');
    });
