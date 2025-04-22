<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\ManagerTransactionController;
use App\Http\Controllers\ManageOrderController;
use App\Http\Controllers\ManageTrashController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CashierTransactionController;

// Public routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Manager routes
Route::middleware(['auth', 'can:manager'])->group(function () {
    Route::get('/manager/dashboard', [DashboardController::class, 'index'])->name('manager.dashboard');
    
    Route::resource('manage_users', ManageUserController::class);
    Route::resource('reports', ReportController::class);
    Route::resource('products', ProductController::class);
    Route::resource('transactions', ManagerTransactionController::class);
});

// Cashier routes
Route::middleware(['auth', 'can:cashier'])->group(function () {
    Route::get('/cashier/dashboard', [ManageOrderController::class, 'index'])->name('cashier.dashboard');
    Route::post('/orders/cancel', [ManageOrderController::class, 'cancel'])->name('order.cancel');
    Route::post('/orders/complete', [ManageOrderController::class, 'complete'])->name('order.complete');
    Route::resource('transaction', CashierTransactionController::class);
    Route::resource('order', ManageOrderController::class);
    Route::resource('trash', ManageTrashController::class);
});