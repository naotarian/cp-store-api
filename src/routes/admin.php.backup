<?php

/*
|--------------------------------------------------------------------------
| Shop Admin API Routes
|--------------------------------------------------------------------------
|
| このファイルは店舗管理者向けのAPIルートを定義します。
| 店舗オーナー・管理者が使用する管理画面専用の機能を提供します。
|
| 主な機能:
| - 店舗管理者認証・パスワード管理
| - 店舗情報の管理・更新
| - ダッシュボード統計情報の表示
| - 店舗運営に関する各種管理機能
|
| アクセス方法: /admin/* のプレフィックスでアクセス
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ShopAdmin\AuthController as ShopAdminAuthController;
use App\Http\Controllers\Auth\ShopAdmin\PasswordController as ShopAdminPasswordController;

/*
|--------------------------------------------------------------------------
| CORS Configuration
|--------------------------------------------------------------------------
|
| 管理画面のフロントエンドからのCORS preflight requests に対応
|
*/

/**
 * CORS preflight requests 処理
 * OPTIONS リクエストに対してCORSヘッダーを返す
 * 
 * @method OPTIONS /admin/{any}
 */
Route::options('{any}', function () {
    return response('', 200);
})->where('any', '.*');

/*
|--------------------------------------------------------------------------
| Shop Admin Authentication Routes
|--------------------------------------------------------------------------
|
| 店舗管理者の認証関連機能
| ログイン、パスワードリセット、セッション管理等
|
*/

Route::prefix('auth')->name('admin.auth.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Public Authentication Routes (認証不要)
    |--------------------------------------------------------------------------
    */
    
    /**
     * 店舗管理者ログイン
     * 管理画面へのログイン認証
     * 
     * @method POST /admin/auth/login
     * @body {
     *   "email": "admin@shop.com",
     *   "password": "password"
     * }
     * @response {
     *   "success": true,
     *   "access_token": "token_string",
     *   "token_type": "Bearer",
     *   "user": {
     *     "id": 1,
     *     "name": "店舗管理者",
     *     "email": "admin@shop.com",
     *     "role": "admin",
     *     "shop": {...}
     *   }
     * }
     */
    Route::post('/login', [ShopAdminAuthController::class, 'login'])->name('login');
    
    /**
     * パスワードリセットメール送信
     * パスワードを忘れた管理者にリセット用メールを送信
     * 
     * @method POST /admin/auth/password/email
     * @body {
     *   "email": "admin@shop.com"
     * }
     */
    Route::post('/password/email', [ShopAdminPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    
    /**
     * パスワードリセット実行
     * リセットトークンを使用してパスワードを更新
     * 
     * @method POST /admin/auth/password/reset
     * @body {
     *   "token": "reset_token",
     *   "email": "admin@shop.com",
     *   "password": "new_password",
     *   "password_confirmation": "new_password"
     * }
     */
    Route::post('/password/reset', [ShopAdminPasswordController::class, 'reset'])->name('password.reset');
    
    /**
     * パスワードリセットトークン検証
     * リセットトークンの有効性をチェック
     * 
     * @method POST /admin/auth/password/validate-token
     * @body {
     *   "token": "reset_token",
     *   "email": "admin@shop.com"
     * }
     */
    Route::post('/password/validate-token', [ShopAdminPasswordController::class, 'validateToken'])->name('password.validate-token');

    /*
    |--------------------------------------------------------------------------
    | Protected Authentication Routes (認証必要)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('auth:shop')->group(function () {
        /**
         * 店舗管理者ログアウト
         * 現在のセッションを終了し、認証トークンを無効化
         * 
         * @method POST /admin/auth/logout
         * @auth Bearer Token (Sanctum)
         */
        Route::post('/logout', [ShopAdminAuthController::class, 'logout'])->name('logout');
        
        /**
         * 認証済み管理者情報取得
         * 現在ログイン中の管理者と店舗情報を取得
         * 
         * @method GET /admin/auth/me
         * @auth Bearer Token (Sanctum)
         * @response {
         *   "success": true,
         *   "user": {
         *     "id": 1,
         *     "name": "店舗管理者",
         *     "email": "admin@shop.com",
         *     "role": "admin",
         *     "shop": {...},
         *     "last_login_at": "2024-01-01T00:00:00.000000Z"
         *   }
         * }
         */
        Route::get('/me', [ShopAdminAuthController::class, 'me'])->name('me');
    });
});

/*
|--------------------------------------------------------------------------
| Shop Management Dashboard Routes
|--------------------------------------------------------------------------
|
| 店舗管理ダッシュボード機能
| 統計情報、アクティビティ、店舗情報管理等
| 全て認証が必要
|
*/

Route::middleware('auth:shop')->name('admin.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard Analytics & Statistics
    |--------------------------------------------------------------------------
    */
    
    /**
     * ダッシュボード統計情報取得
     * 店舗の売上、レビュー、評価、アクティブユーザー等の統計データ
     * 
     * @method GET /admin/dashboard/stats
     * @auth Bearer Token (Sanctum)
     * @response {
     *   "sales": {
     *     "current_month": 234567,
     *     "growth_rate": 12.5
     *   },
     *   "reviews": {
     *     "count": 127,
     *     "growth_rate": 8.3
     *   },
     *   "rating": {
     *     "average": 4.8,
     *     "growth_rate": 0.2
     *   },
     *   "active_users": {
     *     "count": 1284,
     *     "growth_rate": 15.2
     *   }
     * }
     */
    Route::get('/dashboard/stats', function () {
        return response()->json([
            'sales' => [
                'current_month' => 234567,
                'growth_rate' => 12.5
            ],
            'reviews' => [
                'count' => 127,
                'growth_rate' => 8.3
            ],
            'rating' => [
                'average' => 4.8,
                'growth_rate' => 0.2
            ],
            'active_users' => [
                'count' => 1284,
                'growth_rate' => 15.2
            ]
        ]);
    })->name('dashboard.stats');

    /**
     * 最近のアクティビティ取得
     * 店舗に関する最新の活動履歴を取得
     * 
     * @method GET /admin/dashboard/activities
     * @auth Bearer Token (Sanctum)
     * @response [
     *   {
     *     "type": "review",
     *     "message": "新しいレビューが投稿されました",
     *     "time": "5分前",
     *     "icon": "star"
     *   },
     *   {
     *     "type": "user",
     *     "message": "新規ユーザーが登録しました",
     *     "time": "1時間前",
     *     "icon": "users"
     *   }
     * ]
     */
    Route::get('/dashboard/activities', function () {
        return response()->json([
            [
                'type' => 'review',
                'message' => '新しいレビューが投稿されました',
                'time' => '5分前',
                'icon' => 'star'
            ],
            [
                'type' => 'user',
                'message' => '新規ユーザーが登録しました',
                'time' => '1時間前',
                'icon' => 'users'
            ],
            [
                'type' => 'sales',
                'message' => '売上が目標を達成しました',
                'time' => '3時間前',
                'icon' => 'trending-up'
            ]
        ]);
    })->name('dashboard.activities');

    /*
    |--------------------------------------------------------------------------
    | Shop Information Management
    |--------------------------------------------------------------------------
    */

    /**
     * 管理中の店舗情報取得
     * 現在ログイン中の管理者が管理している店舗の詳細情報を取得
     * 
     * @method GET /admin/shop
     * @auth Bearer Token (Sanctum)
     * @response {
     *   "id": 1,
     *   "name": "サンプル店舗",
     *   "slug": "sample-shop",
     *   "description": "店舗の説明文",
     *   "address": "東京都渋谷区...",
     *   "phone": "03-1234-5678",
     *   "email": "info@sample-shop.com",
     *   "website": "https://sample-shop.com",
     *   "opening_hours": {...},
     *   "created_at": "2024-01-01T00:00:00.000000Z",
     *   "updated_at": "2024-01-01T00:00:00.000000Z"
     * }
     */
    Route::get('/shop', function (\Illuminate\Http\Request $request) {
        // 現在ログイン中の管理者の店舗情報を取得
        $user = $request->user();
        return response()->json($user->shop);
    })->name('shop.show');

    /**
     * 店舗情報更新
     * 管理中の店舗の基本情報を更新
     * 
     * @method PUT /admin/shop
     * @auth Bearer Token (Sanctum)
     * @body {
     *   "name": "新しい店舗名",
     *   "description": "更新された店舗説明",
     *   "address": "新しい住所",
     *   "phone": "03-9876-5432",
     *   "email": "new-info@shop.com",
     *   "website": "https://new-shop.com",
     *   "opening_hours": {
     *     "monday": {"open": "09:00", "close": "18:00"},
     *     "tuesday": {"open": "09:00", "close": "18:00"},
     *     ...
     *   }
     * }
     * @todo 実装予定 - 現在はモックレスポンス
     */
    Route::put('/shop', function () {
        // 店舗情報の更新
        // TODO: 実装予定
        return response()->json(['message' => '店舗情報を更新しました']);
    })->name('shop.update');

    /*
    |--------------------------------------------------------------------------
    | Coupon Management Routes
    |--------------------------------------------------------------------------
    |
    | クーポン管理機能
    | 登録されているクーポン一覧、現在発行中のクーポン、スケジュール管理
    | 全て認証が必要
    |
    */

    Route::prefix('coupons')->name('coupons.')->group(function () {
        /**
         * 店舗の全クーポン一覧取得
         * 登録されているクーポンのマスタデータを全て取得
         * 
         * @method GET /admin/coupons
         * @auth Bearer Token (Sanctum)
         * @response {
         *   "status": "success",
         *   "data": {
         *     "coupons": [
         *       {
         *         "id": "01HWFGJ8K3X...",
         *         "title": "ドリンク10%OFF",
         *         "description": "全ドリンクメニューが10%割引になります",
         *         "discount_type": "percentage",
         *         "discount_value": 10,
         *         "formatted_discount": "10%OFF",
         *         "conditions": "他のクーポンとの併用不可",
         *         "image_url": "https://...",
         *         "is_active": true,
         *         "created_at": "2024-01-01 00:00:00",
         *         "updated_at": "2024-01-01 00:00:00",
         *         "active_issues_count": 2,
         *         "schedules_count": 1,
         *         "total_issues_count": 15
         *       }
         *     ]
         *   }
         * }
         */
        Route::get('/', [App\Http\Controllers\Admin\CouponController::class, 'index'])->name('index');

        /**
         * 新しいクーポンを作成
         * 
         * @method POST /admin/coupons
         * @auth Bearer Token (Sanctum)
         * @body {
         *   "title": "ドリンク10%OFF",
         *   "description": "全ドリンクメニューが10%割引になります",
         *   "discount_type": "percentage",
         *   "discount_value": 10,
         *   "conditions": "他のクーポンとの併用不可",
         *   "image_url": "https://..."
         * }
         * @response {
         *   "status": "success",
         *   "message": "クーポンを作成しました",
         *   "data": {
         *     "coupon": {
         *       "id": "01HWFGJ8K3X...",
         *       "title": "ドリンク10%OFF",
         *       "description": "全ドリンクメニューが10%割引になります",
         *       "discount_type": "percentage",
         *       "discount_value": 10,
         *       "formatted_discount": "10%OFF",
         *       "conditions": "他のクーポンとの併用不可",
         *       "image_url": "https://...",
         *       "is_active": true,
         *       "created_at": "2024-01-01 00:00:00",
         *       "updated_at": "2024-01-01 00:00:00"
         *     }
         *   }
         * }
         */
        Route::post('/', [App\Http\Controllers\Admin\CouponController::class, 'store'])->name('store');

        /**
         * クーポンを更新
         * 
         * @method PUT /admin/coupons/{id}
         * @auth Bearer Token (Sanctum)
         * @param {string} id - クーポンID
         * @body {
         *   "title": "ドリンク15%OFF",
         *   "description": "全ドリンクメニューが15%割引になります",
         *   "conditions": "他のクーポンとの併用不可",
         *   "notes": "スタッフ向けの補足情報",
         *   "image_url": "https://..."
         * }
         * @response {
         *   "status": "success",
         *   "message": "クーポンを更新しました",
         *   "data": {
         *     "coupon": {
         *       "id": "01HWFGJ8K3X...",
         *       "title": "ドリンク15%OFF",
         *       "description": "全ドリンクメニューが15%割引になります",
         *       "conditions": "他のクーポンとの併用不可",
         *       "notes": "スタッフ向けの補足情報",
         *       "image_url": "https://...",
         *       "is_active": true,
         *       "created_at": "2024-01-01 00:00:00",
         *       "updated_at": "2024-01-01 00:00:00"
         *     }
         *   }
         * }
         */
        Route::put('/{id}', [App\Http\Controllers\Admin\CouponController::class, 'update'])->name('update');

        /**
         * 現在発行中のクーポン一覧取得
         * アクティブで利用可能なクーポン発行を全て取得
         * 
         * @method GET /admin/coupons/active-issues
         * @auth Bearer Token (Sanctum)
         * @response {
         *   "status": "success",
         *   "data": {
         *     "active_issues": [
         *       {
         *         "id": "01HWFGJ8K3X...",
         *         "coupon_id": "01HWFGJ8K3X...",
         *         "issue_type": "manual",
         *         "start_time": "2024-01-01 14:00:00",
         *         "end_time": "2024-01-01 17:00:00",
         *         "duration_minutes": 180,
         *         "max_acquisitions": 20,
         *         "current_acquisitions": 5,
         *         "remaining_count": 15,
         *         "time_remaining": "2時間30分",
         *         "is_available": true,
         *         "status": "active",
         *         "issued_at": "2024-01-01 14:00:00",
         *         "coupon": {
         *           "id": "01HWFGJ8K3X...",
         *           "title": "ドリンク10%OFF",
         *           "formatted_discount": "10%OFF"
         *         },
         *         "issuer": {
         *           "id": 1,
         *           "name": "店舗管理者"
         *         }
         *       }
         *     ]
         *   }
         * }
         */
        Route::get('/active-issues', [App\Http\Controllers\Admin\CouponController::class, 'activeIssues'])->name('active-issues');

        /**
         * スケジュール設定されたクーポン一覧取得
         * バッチ処理で実行されるクーポンスケジュールを全て取得
         * 
         * @method GET /admin/coupons/schedules
         * @auth Bearer Token (Sanctum)
         * @response {
         *   "status": "success",
         *   "data": {
         *     "schedules": [
         *       {
         *         "id": "01HWFGJ8K3X...",
         *         "coupon_id": "01HWFGJ8K3X...",
         *         "schedule_name": "平日午後の空席時間",
         *         "day_type": "weekdays",
         *         "day_type_display": "平日のみ",
         *         "start_time": "14:00",
         *         "end_time": "17:00",
         *         "time_range_display": "14:00 - 17:00",
         *         "duration_minutes": 180,
         *         "max_acquisitions": 20,
         *         "valid_from": "2024-01-01",
         *         "valid_until": null,
         *         "is_active": true,
         *         "last_batch_processed_date": "2024-01-01",
         *         "coupon": {
         *           "id": "01HWFGJ8K3X...",
         *           "title": "ドリンク10%OFF",
         *           "formatted_discount": "10%OFF"
         *         }
         *       }
         *     ]
         *   }
         * }
         */
        Route::get('/schedules', [App\Http\Controllers\Admin\CouponController::class, 'schedules'])->name('schedules');
        
        /**
         * スケジュール作成
         */
        Route::post('/schedules', [App\Http\Controllers\Admin\CouponController::class, 'createSchedule'])->name('schedules.create');
        
        /**
         * スケジュール更新
         */
        Route::put('/schedules/{id}', [App\Http\Controllers\Admin\CouponController::class, 'updateSchedule'])->name('schedules.update');
        
        /**
         * スケジュール削除
         */
        Route::delete('/schedules/{id}', [App\Http\Controllers\Admin\CouponController::class, 'deleteSchedule'])->name('schedules.delete');
        
        /**
         * スケジュールの有効/無効切り替え
         */
        Route::patch('/schedules/{id}/toggle-status', [App\Http\Controllers\Admin\CouponController::class, 'toggleScheduleStatus'])->name('schedules.toggle-status');
        
        /**
         * 今日のスケジュール一覧取得
         */
        Route::get('/schedules/today', [App\Http\Controllers\Admin\CouponController::class, 'todaySchedules'])->name('schedules.today');

        /**
         * クーポンの即時発行（スポット発行）
         * 指定したクーポンを今すぐ発行開始
         * 
         * @method POST /admin/coupons/{couponId}/issue-now
         * @auth Bearer Token (Sanctum)
         * @param {string} couponId - クーポンID
         * @body {
         *   "duration_minutes": 180,
         *   "max_acquisitions": 20
         * }
         * @response {
         *   "status": "success",
         *   "message": "クーポンを発行しました",
         *   "data": {
         *     "issue_id": "01HWFGJ8K3X...",
         *     "end_time": "2024-01-01 17:00:00"
         *   }
         * }
         */
        Route::post('/{couponId}/issue-now', [App\Http\Controllers\Admin\CouponController::class, 'issueNow'])->name('issue-now');

        /**
         * クーポン発行の停止
         * 現在発行中のクーポンを手動で停止
         * 
         * @method POST /admin/coupons/issues/{issueId}/stop
         * @auth Bearer Token (Sanctum)
         * @param {string} issueId - クーポン発行ID
         * @response {
         *   "status": "success",
         *   "message": "クーポン発行を停止しました"
         * }
         */
        Route::post('/issues/{issueId}/stop', [App\Http\Controllers\Admin\CouponController::class, 'stopIssue'])->name('stop-issue');

        /**
         * クーポン取得通知一覧を取得
         * 
         * @method GET /admin/coupons/acquisition-notifications
         * @auth Bearer Token (Sanctum)
         * @response {
         *   "status": "success",
         *   "data": {
         *     "notifications": [
         *       {
         *         "id": "01HWFGJ8K3X...",
         *         "user_id": "01HWFGJ8K3X...",
         *         "user_name": "山田太郎",
         *         "user_avatar": "https://example.com/avatar.jpg",
         *         "acquired_at": "2024-01-15T10:30:00Z",
         *         "is_read": false,
         *         "coupon_issue": {
         *           "id": "01HWFGJ8K3X...",
         *           "issue_type": "manual",
         *           "coupon": {
         *             "id": "01HWFGJ8K3X...",
         *             "title": "ドリンク10%OFF",
         *             "description": "全ドリンクメニューが10%割引"
         *           }
         *         }
         *       }
         *     ],
         *     "unread_count": 5
         *   }
         * }
         */
        Route::get('/acquisition-notifications', [App\Http\Controllers\Admin\CouponController::class, 'acquisitionNotifications'])->name('acquisition-notifications');

        /**
         * 未読のクーポン取得通知のみを取得（バナー表示用）
         * 
         * @method GET /admin/coupons/unread-notifications
         * @auth Bearer Token (Sanctum)
         * @response {
         *   "status": "success",
         *   "data": {
         *     "notifications": [
         *       {
         *         "id": "01HWFGJ8K3X...",
         *         "user_id": "01HWFGJ8K3X...",
         *         "user_name": "山田太郎",
         *         "user_avatar": "https://example.com/avatar.jpg",
         *         "acquired_at": "2024-01-15T10:30:00Z",
         *         "is_read": false,
         *         "coupon_issue": {
         *           "id": "01HWFGJ8K3X...",
         *           "issue_type": "manual",
         *           "coupon": {
         *             "id": "01HWFGJ8K3X...",
         *             "title": "ドリンク10%OFF",
         *             "description": "全ドリンクメニューが10%割引"
         *           }
         *         }
         *       }
         *     ],
         *     "unread_count": 3
         *   }
         * }
         */
        Route::get('/unread-notifications', [App\Http\Controllers\Admin\CouponController::class, 'unreadNotifications'])->name('unread-notifications');

        /**
         * 取得通知を既読にする
         * 
         * @method POST /admin/coupons/acquisition-notifications/{notificationId}/read
         * @auth Bearer Token (Sanctum)
         * @param {string} notificationId - 通知ID（取得記録ID）
         * @response {
         *   "status": "success",
         *   "message": "通知を既読にしました"
         * }
         */
        Route::post('/acquisition-notifications/{notificationId}/read', [App\Http\Controllers\Admin\CouponController::class, 'markNotificationAsRead'])->name('acquisition-notifications.read');

        /**
         * 全ての取得通知を既読にする
         * 
         * @method POST /admin/coupons/acquisition-notifications/read-all
         * @auth Bearer Token (Sanctum)
         * @response {
         *   "status": "success",
         *   "message": "5件の通知を既読にしました",
         *   "data": {
         *     "updated_count": 5
         *   }
         * }
         */
        Route::post('/acquisition-notifications/read-all', [App\Http\Controllers\Admin\CouponController::class, 'markAllNotificationsAsRead'])->name('acquisition-notifications.read-all');

        /**
         * 通知をバナー表示済みにする
         * 
         * @method POST /admin/coupons/acquisition-notifications/{notificationId}/banner-shown
         * @auth Bearer Token (Sanctum)
         * @param {string} notificationId - 通知ID（取得記録ID）
         * @response {
         *   "status": "success",
         *   "message": "通知をバナー表示済みにしました"
         * }
         */
        Route::post('/acquisition-notifications/{notificationId}/banner-shown', [App\Http\Controllers\Admin\CouponController::class, 'markBannerShown'])->name('acquisition-notifications.banner-shown');
    });
}); 