<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MidtransCallbackController;
// use App\Http\Controllers\MidtransController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController as UserProductController;
use App\Http\Controllers\ProductReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/midtrans/handle', [MidtransCallbackController::class, 'handle']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User Routes
    Route::middleware('check-role:1')->prefix('user')->group(function () {
        // Produk
        Route::get('/product/grouped', [UserProductController::class, 'groupedProducts']);
        Route::get('/product/{id}', [ProductController::class, 'show']);

        // Keranjang
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/add', [CartController::class, 'store']);
        Route::put('/cart/update/{id}', [CartController::class, 'update']);
        Route::delete('/cart/delete-multiple', [CartController::class, 'destroyMultiple']);
        Route::delete('/cart/clear', [CartController::class, 'clear']);

        // Pemesanan
        Route::get('/order', [OrderController::class, 'index']);
        Route::post('/order', [OrderController::class, 'store']);
        Route::get('/order/{id}', [OrderController::class, 'show']);
        Route::get('/order/{id}/pay', [OrderController::class, 'pay']);
        Route::post('/order/repay/{order}', [OrderController::class, 'repay']);

        // Review Produk
        Route::get('/review/{product}', [ProductReviewController::class, 'index']);
        Route::post('/review', [ProductReviewController::class, 'store']);
        Route::put('/review/{id}', [ProductReviewController::class, 'update']);
        Route::delete('/review/{id}', [ProductReviewController::class, 'destroy']);
    });

    // Admin Routes
    Route::middleware('check-role:2')->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/latestOrder', [DashboardController::class, 'latestOrders']);
        // Kelola Produk 
        Route::get('/product', [ProductController::class, 'index']);
        Route::get('/product/{id}', [ProductController::class, 'show']);
        Route::post('/product', [ProductController::class, 'store']);
        Route::post('/product/{id}', [ProductController::class, 'update']);
        Route::post('/product/promoted/{id}', [ProductController::class, 'setPromoted']);
        Route::delete('/product/{id}', [ProductController::class, 'delete']);

        // Kelola Kategori Produk
        Route::get('/category', [ProductCategoryController::class, 'index']);
        Route::get('/category/{id}', [ProductCategoryController::class, 'show']);
        Route::post('/category', [ProductCategoryController::class, 'store']);
        Route::post('/category/{id}', [ProductCategoryController::class, 'update']);
        Route::delete('/category/{id}', [ProductCategoryController::class, 'delete']);

        // Kelola Pesanan
        Route::get('/order', [AdminOrderController::class, 'index']);
        Route::get('/order/{id}', [AdminOrderController::class, 'show']);
        // Route::put('/order/{id}/status', [AdminOrderController::class, 'updateStatus']);
    });
});
