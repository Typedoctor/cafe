<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ManageOrderController;
use App\Http\Controllers\ManageTrashController;

//for login functionality
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/manager/dashboard', function() {
    return view('manager.dashboard');
})->name('manager.dashboard')->middleware('auth');

Route::get('/cashier/dashboard', function() {
    return view('cashier.dashboard');
})->name('cashier.dashboard')->middleware('auth');

//adi it para liwat inventory management
Route::resource('products', ProductController::class);
Route::resource('manage_users', ManageUserController::class);
Route::resource('order', ManageOrderController::class);
Route::resource('trash', ManageTrashController::class);

// for middleware kernel inin ensure na rolebased talaga
Route::middleware(['role:manager'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');

});

//for viewing dashboard
Route::get('/home', function() {return view('manager.dashboard');})->name('dashboard');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/manage_users', [ManageUserController::class, 'index'])->name('manage_users.index');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('transactions',[TransactionController::class,'index'])->name('transactions.index');
