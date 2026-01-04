<?php

use Illuminate\Support\Facades\Route;
use TmrEcosystem\HRM\Presentation\Http\Controllers\EmployeeController;

/*
|--------------------------------------------------------------------------
| HRM Routes
|--------------------------------------------------------------------------
|
| รวม Route ทั้งหมดที่เกี่ยวกับ HRM Domain
|
*/

Route::middleware(['web', 'auth', 'verified']) // กำหนด Middleware ที่จำเป็น
    ->prefix('hrm')                      // กำหนด Prefix URL (เช่น /inventory/dashboard)
    ->name('hrm.')                       // กำหนด Prefix Name (เช่น inventory.dashboard)
    ->group(function () {

        Route::resource('employees', EmployeeController::class);
    });
