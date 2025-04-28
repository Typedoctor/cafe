<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\ManagerTransactionController;
use App\Http\Controllers\CashierManageOrderController;
use App\Http\Controllers\ManageTrashController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CashierTransactionController;
use App\Http\Controllers\ManagerShelfController;
use App\Http\Controllers\ManagerDamagedProductController;

// Public routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Manager routes
//Route::middleware(['auth', 'can:manager'])->group(function () {
    Route::get('/manager/dashboard', [DashboardController::class, 'index'])->name('manager.dashboard');
    
    Route::resource('damaged-items',  ManagerDamagedProductController::class);
    Route::resource('add-to-shelf',  ManagerShelfController::class);
    Route::resource('manage_users', ManageUserController::class);
    Route::resource('reports', ReportController::class);
    Route::resource('products', ProductController::class);
    Route::resource('transactions', ManagerTransactionController::class);
//});

// Cashier routes
//Route::middleware(['auth', 'can:cashier'])->group(function () {
    Route::get('/cashier/dashboard', [CashierManageOrderController::class, 'index'])->name('cashier.dashboard');
    Route::post('/orders/cancel', [CashierManageOrderController::class, 'cancel'])->name('order.cancel');
    Route::post('/orders/complete', [CashierManageOrderController::class, 'complete'])->name('order.complete');
    Route::get('cashier/transactions', [CashierTransactionController::class, 'index_cashier'])->name('cashier.cashier_transactions');
    //Route::get('cashier/transactions/export', [CashierTransactionController::class, 'export_cashier'])->name('cashier.transactions.export');
    Route::post('/add-to-shelf/check', [ManagerShelfController::class, 'check'])->name('add-to-shelf.check');
    Route::resource('order', CashierManageOrderController::class);
    Route::resource('trash', ManageTrashController::class);
//});