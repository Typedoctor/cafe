<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ManageOrderController;
use App\Http\Controllers\ManageTrashController;

// Public routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Manager routes
Route::middleware(['auth', 'can:manager'])->group(function () {
    Route::get('/manager/dashboard', function() {
        return view('manager.dashboard');
    })->name('manager.dashboard');
    
    Route::resource('manage_users', ManageUserController::class);
    Route::resource('reports', ReportController::class);
    Route::resource('products', ProductController::class);
    Route::resource('transactions', TransactionController::class);
});

// Cashier routes
Route::middleware(['auth', 'can:cashier'])->group(function () {
    Route::get('/cashier/dashboard', function() {
        return view('cashier.dashboard');
    })->name('cashier.dashboard');
    
    Route::resource('order', ManageOrderController::class);
    Route::resource('trash', ManageTrashController::class);
});
