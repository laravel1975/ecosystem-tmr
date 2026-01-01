<?php

use Illuminate\Support\Facades\Route;
use TmrEcosystem\Sales\Presentation\Http\Controllers\SalesOrderController;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('sales')
    ->name('sales.')
    ->group(function () {
        Route::get('/orders', [SalesOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [SalesOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [SalesOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}', [SalesOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/confirm', [SalesOrderController::class, 'confirm'])->name('orders.confirm');
    });
