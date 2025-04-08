<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

//for login functionality
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process'); // Add this!
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/manager/dashboard', function() {
    return view('manager.dashboard');
})->name('manager.dashboard')->middleware('auth');

Route::get('/cashier/dashboard', function() {
    return view('cashier.dashboard');
})->name('cashier.dashboard')->middleware('auth');

//adi it para liwat inventory management
Route::resource('products', ProductController::class);


// for middleware kernel inin ensure na rolebased talaga
Route::middleware(['role:manager'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/users/manage', [UserController::class, 'index'])->name('users.manage');
});

//for viewing dashboard
Route::get('/home', function() {
    return view('home');
})->name('dashboard');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
