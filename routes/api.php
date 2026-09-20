<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Payment\ZaloPayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\StripeController;
/*
|--------------------------------------------------------------------------
| Payment Webhooks
|--------------------------------------------------------------------------
*/

Route::post('/sepay/webhook', [
    CheckoutController::class,
    'sepayWebhook',
])->name('sepay.webhook');

Route::post('/zalopay/callback', [
    ZaloPayController::class,
    'callback',
])->name('zalopay.callback');


/*
|--------------------------------------------------------------------------
| API V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product:slug}', [ProductController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::patch('/cart/{cartItem}', [CartController::class, 'update']);
        Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);

    Route::post(
    '/stripe/webhook',
    [StripeController::class, 'webhook']
)->name('stripe.webhook');
    });
});