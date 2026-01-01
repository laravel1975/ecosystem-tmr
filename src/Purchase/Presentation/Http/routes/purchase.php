<?php

use Illuminate\Support\Facades\Route;
use TmrEcosystem\Purchase\Presentation\Http\Controllers\PurchaseOrderController;

/*
|--------------------------------------------------------------------------
| Purchase Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('purchase')
    ->name('purchase.')
    ->group(function () {

        // PO CRUD
        Route::get('/orders', [PurchaseOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [PurchaseOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [PurchaseOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}', [PurchaseOrderController::class, 'show'])->name('orders.show');

        // Action: Confirm
        Route::post('/orders/{id}/confirm', [PurchaseOrderController::class, 'confirm'])->name('orders.confirm');
    });
