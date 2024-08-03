<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;

Route::get('/', function () {
    return view('welcome');
})->middleware(['verify.shopify'])->name('home');

Route::get('shopify/products', [ShopifyController::class, 'getAllProducts'])->name('shopify.products')->middleware(['verify.shopify']);