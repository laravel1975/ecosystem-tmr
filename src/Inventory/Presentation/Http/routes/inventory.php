<?php

use Illuminate\Support\Facades\Route;
use TmrEcosystem\Inventory\Presentation\Http\Controllers\InventoryDashboardController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Inventory Routes
});
Route::get('/inventory', InventoryDashboardController::class)->name('inventory.dashboard');
