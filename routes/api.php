<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('orders/{order}/add', [OrderController::class, 'addProduct'])->name('orders.add-product');
Route::post('orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
Route::resource('orders', OrderController::class);
