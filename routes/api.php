<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;



// テスト用エンドポイント
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
});

// 認証エンドポイント
Route::prefix('auth')->group(function () {
    // 認証不要のエンドポイント
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // 認証が必要なエンドポイント
    Route::middleware(\App\Http\Middleware\ApiTokenMiddleware::class)->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });
});

// 店舗関連エンドポイント
Route::apiResource('shops', ShopController::class);
Route::get('shops/{shopId}/reviews', [ReviewController::class, 'getByShop']);

// レビュー関連エンドポイント
Route::get('reviews', [ReviewController::class, 'index']);
Route::post('reviews', [ReviewController::class, 'store'])->middleware(\App\Http\Middleware\ApiTokenMiddleware::class);

// お気に入り関連エンドポイント
Route::prefix('favorites')->middleware(\App\Http\Middleware\ApiTokenMiddleware::class)->group(function () {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/', [FavoriteController::class, 'store']);
    Route::delete('/{shopId}', [FavoriteController::class, 'destroy']);
    Route::post('/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/check/{shopId}', [FavoriteController::class, 'check']);
}); 