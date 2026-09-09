<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', InventoryController::class)->name('inventory.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
