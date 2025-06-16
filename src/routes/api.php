<?php

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| このファイルはモバイルアプリケーション用のAPIルートを定義します。
| エンドユーザー（顧客）向けの機能を提供します。
|
| 主な機能:
| - ユーザー認証・プロフィール管理
| - 店舗情報の閲覧
| - レビューの投稿・閲覧・管理
| - お気に入り店舗の管理
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Auth\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\Mobile\FavoriteController;
use App\Http\Controllers\Mobile\CouponController;

/*
|--------------------------------------------------------------------------
| System & Development Routes
|--------------------------------------------------------------------------
| 
| システム稼働確認及び開発用のエンドポイント
|
*/

/**
 * API接続テスト用エンドポイント
 * アプリケーションとAPIサーバーの疎通確認に使用
 * 
 * @method GET /api/test
 * @response {
 *   "message": "API is working!",
 *   "timestamp": "2024-01-01T00:00:00.000000Z"
 * }
 */
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()
    ])->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
});

/*
|--------------------------------------------------------------------------
| Mobile User Authentication Routes
|--------------------------------------------------------------------------
|
| モバイルアプリユーザー（顧客）の認証関連機能
| 新規登録、ログイン、プロフィール管理等
|
*/

Route::prefix('auth')->name('api.auth.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Public Authentication Routes (認証不要)
    |--------------------------------------------------------------------------
    */
    
    /**
     * ユーザー新規登録
     * 新しい顧客アカウントを作成
     * 
     * @method POST /api/auth/register
     * @body {
     *   "name": "山田太郎",
     *   "email": "yamada@example.com",
     *   "password": "password",
     *   "password_confirmation": "password"
     * }
     */
    Route::post('/register', [MobileAuthController::class, 'register'])->name('register');
    
    /**
     * ユーザーログイン
     * 既存顧客のログイン認証
     * 
     * @method POST /api/auth/login
     * @body {
     *   "email": "yamada@example.com",
     *   "password": "password"
     * }
     */
    Route::post('/login', [MobileAuthController::class, 'login'])->name('login');

    /*
    |--------------------------------------------------------------------------
    | Protected Authentication Routes (認証必要)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(\App\Http\Middleware\ApiTokenMiddleware::class)->group(function () {
        /**
         * ユーザーログアウト
         * 現在のセッションを終了し、認証トークンを無効化
         * 
         * @method POST /api/auth/logout
         * @auth Bearer Token
         */
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');
        
        /**
         * 認証済みユーザー情報取得
         * 現在ログイン中のユーザー情報を取得
         * 
         * @method GET /api/auth/user
         * @auth Bearer Token
         */
        Route::get('/user', [MobileAuthController::class, 'user'])->name('user');
        
        /**
         * プロフィール更新
         * ユーザーの個人情報を更新
         * 
         * @method PUT /api/auth/profile
         * @auth Bearer Token
         * @body {
         *   "name": "山田花子",
         *   "email": "hanako@example.com"
         * }
         */
        Route::put('/profile', [MobileAuthController::class, 'updateProfile'])->name('profile.update');
    });
});

/*
|--------------------------------------------------------------------------
| Shop Information Routes
|--------------------------------------------------------------------------
|
| 店舗情報の取得・閲覧機能
| 顧客が店舗を検索・閲覧するためのエンドポイント
|
*/

/**
 * 店舗情報の全操作（RESTfulリソース）
 * 
 * @routes
 *   GET    /api/shops           - 店舗一覧取得（検索・フィルタリング対応）
 *   GET    /api/shops/{id}      - 特定店舗の詳細情報取得
 */
Route::apiResource('shops', ShopController::class)->names([
    'index' => 'api.shops.index',
    'show' => 'api.shops.show',
]);

/**
 * 特定店舗のレビュー一覧取得
 * 指定された店舗に対するすべてのレビューを取得
 * 
 * @method GET /api/shops/{shopId}/reviews
 * @param {int} shopId - 店舗ID
 */
Route::get('shops/{shopId}/reviews', [ReviewController::class, 'getByShop'])->name('api.shops.reviews');

/*
|--------------------------------------------------------------------------
| Review Management Routes
|--------------------------------------------------------------------------
|
| レビュー・評価機能
| 顧客による店舗レビューの投稿・閲覧・管理
|
*/

/**
 * 全店舗のレビュー一覧取得（公開）
 * ページネーション・並び替え対応
 * 
 * @method GET /api/reviews
 * @query {
 *   "page": 1,
 *   "per_page": 10,
 *   "sort": "created_at",
 *   "order": "desc"
 * }
 */
Route::get('reviews', [ReviewController::class, 'index'])->name('api.reviews.index');

/**
 * 特定レビューの詳細取得（公開）
 * 
 * @method GET /api/reviews/{id}
 * @param {int} id - レビューID
 */
Route::get('reviews/{id}', [ReviewController::class, 'show'])->name('api.reviews.show');

/*
|--------------------------------------------------------------------------
| Protected Review Routes (認証必要)
|--------------------------------------------------------------------------
*/

/**
 * 新規レビュー投稿
 * 認証済みユーザーが店舗にレビューを投稿
 * 
 * @method POST /api/reviews
 * @auth Bearer Token
 * @body {
 *   "shop_id": 1,
 *   "rating": 5,
 *   "comment": "とても美味しかったです！",
 *   "visit_date": "2024-01-01"
 * }
 */
Route::post('reviews', [ReviewController::class, 'store'])
    ->middleware(\App\Http\Middleware\ApiTokenMiddleware::class)
    ->name('api.reviews.store');

/**
 * レビュー更新
 * 自分が投稿したレビューの内容を更新
 * 
 * @method PUT /api/reviews/{id}
 * @auth Bearer Token
 * @param {int} id - レビューID
 */
Route::put('reviews/{id}', [ReviewController::class, 'update'])
    ->middleware(\App\Http\Middleware\ApiTokenMiddleware::class)
    ->name('api.reviews.update');

/**
 * レビュー削除
 * 自分が投稿したレビューを削除
 * 
 * @method DELETE /api/reviews/{id}
 * @auth Bearer Token
 * @param {int} id - レビューID
 */
Route::delete('reviews/{id}', [ReviewController::class, 'destroy'])
    ->middleware(\App\Http\Middleware\ApiTokenMiddleware::class)
    ->name('api.reviews.destroy');

/*
|--------------------------------------------------------------------------
| Favorite Shops Routes
|--------------------------------------------------------------------------
|
| お気に入り店舗管理機能
| 顧客が気に入った店舗をブックマーク・管理
| 全て認証が必要
|
*/

Route::prefix('favorites')
    ->middleware(\App\Http\Middleware\ApiTokenMiddleware::class)
    ->name('api.favorites.')
    ->group(function () {
        
        /**
         * お気に入り店舗一覧取得
         * 現在のユーザーがお気に入りに追加した全店舗を取得
         * 
         * @method GET /api/favorites
         * @auth Bearer Token
         */
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        
        /**
         * 店舗をお気に入りに追加
         * 
         * @method POST /api/favorites
         * @auth Bearer Token
         * @body {
         *   "shop_id": 1
         * }
         */
        Route::post('/', [FavoriteController::class, 'store'])->name('store');
        
        /**
         * お気に入りから店舗を削除
         * 
         * @method DELETE /api/favorites/{shopId}
         * @auth Bearer Token
         * @param {int} shopId - 店舗ID
         */
        Route::delete('/{shopId}', [FavoriteController::class, 'destroy'])->name('destroy');
        
        /**
         * お気に入り状態のトグル
         * お気に入りに登録済みなら削除、未登録なら追加
         * 
         * @method POST /api/favorites/toggle
         * @auth Bearer Token
         * @body {
         *   "shop_id": 1
         * }
         */
        Route::post('/toggle', [FavoriteController::class, 'toggle'])->name('toggle');
        
        /**
         * お気に入り状態の確認
         * 指定した店舗がお気に入りに登録されているかチェック
         * 
         * @method GET /api/favorites/check/{shopId}
         * @auth Bearer Token
         * @param {int} shopId - 店舗ID
         * @response {
         *   "is_favorite": true
         * }
         */
        Route::get('/check/{shopId}', [FavoriteController::class, 'check'])->name('check');
    });

/*
|--------------------------------------------------------------------------
| Coupon Routes
|--------------------------------------------------------------------------
|
| クーポン機能
| 顧客がクーポンを閲覧・取得・利用するためのエンドポイント
|
*/

/**
 * 特定店舗のクーポン一覧取得（公開）
 * 指定された店舗で利用可能なクーポンを取得
 * 
 * @method GET /api/shops/{shopId}/coupons
 * @param {int} shopId - 店舗ID
 */
Route::get('shops/{shopId}/coupons', [CouponController::class, 'getShopCoupons'])->name('api.shops.coupons');

/**
 * 特定店舗の現在発行中のクーポン一覧取得（公開）
 * 指定された店舗で現在取得可能なクーポン発行情報を取得
 * 
 * @method GET /api/shops/{shopId}/active-issues
 * @param {int} shopId - 店舗ID
 */
Route::get('shops/{shopId}/active-issues', [CouponController::class, 'getActiveIssues'])->name('api.shops.active-issues');

/*
|--------------------------------------------------------------------------
| Protected Coupon Routes (認証必要)
|--------------------------------------------------------------------------
*/

Route::middleware(\App\Http\Middleware\ApiTokenMiddleware::class)->group(function () {
    /**
     * クーポンを取得
     * 発行中のクーポンを取得してユーザーのクーポン一覧に追加
     * 
     * @method POST /api/coupon-issues/{issueId}/acquire
     * @auth Bearer Token
     * @param {string} issueId - クーポン発行ID
     */
    Route::post('coupon-issues/{issueId}/acquire', [CouponController::class, 'acquireCoupon'])->name('api.coupon-issues.acquire');
    
    /**
     * ユーザーの取得済みクーポン一覧取得
     * 現在のユーザーが取得した全クーポンを取得
     * 
     * @method GET /api/user/coupons
     * @auth Bearer Token
     */
    Route::get('user/coupons', [CouponController::class, 'getUserCoupons'])->name('api.user.coupons');
    
    /**
     * クーポンを使用
     * 取得済みクーポンを使用済み状態に変更
     * 
     * @method POST /api/user/coupons/{acquisitionId}/use
     * @auth Bearer Token
     * @param {string} acquisitionId - クーポン取得ID
     */
    Route::post('user/coupons/{acquisitionId}/use', [CouponController::class, 'useCoupon'])->name('api.user.coupons.use');
});


