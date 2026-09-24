<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', InventoryController::class)->name('inventory.index');
Route::get('/producto/{product}', [InventoryController::class, 'show'])->name('inventory.show');
Route::get('/bitcoin/cotizacion', [CheckoutController::class, 'bitcoinQuote'])->name('bitcoin.quote');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
