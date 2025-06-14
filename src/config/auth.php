<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'shop'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | CP-Store 認証ガード設計:
    | 
    | 1. api - エンドユーザー (モバイル+Web)
    | 2. sanctum - 店舗管理者 
    | 3. root_sanctum - 運営管理者 (将来実装)
    |
    */

    'guards' => [
        // Web用（未使用）
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        
        // エンドユーザー認証（モバイルアプリ + Web版）
        'user' => [
            'driver' => 'session',
            'provider' => 'users',
            'hash' => false,
        ],
        
        // 店舗管理者認証（店舗管理画面）
        'shop' => [
            'driver' => 'sanctum',
            'provider' => 'shop_admins',
        ],
        
        // 運営管理者認証（将来実装予定）
        // 'root' => [
        //     'driver' => 'sanctum',
        //     'provider' => 'roots',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | CP-Store ユーザープロバイダー設計:
    | 
    | 1. users - エンドユーザー (User モデル)
    | 2. shop_admins - 店舗管理者 (ShopAdmin モデル)  
    | 3. roots - 運営管理者 (Root モデル - 将来実装)
    |
    */

    'providers' => [
        // エンドユーザープロバイダー
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        
        // 店舗管理者プロバイダー
        'shop_admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\ShopAdmin::class,
        ],
        
        // 運営管理者プロバイダー（将来実装予定）
        // 'roots' => [
        //     'driver' => 'eloquent', 
        //     'model' => App\Models\Root::class,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        // エンドユーザー用パスワードリセット
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        
        // 店舗管理者用パスワードリセット
        'shop_admins' => [
            'provider' => 'shop_admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        
        // 運営管理者用パスワードリセット（将来実装予定）
        // 'roots' => [
        //     'provider' => 'roots',
        //     'table' => 'password_reset_tokens', 
        //     'expire' => 60,
        //     'throttle' => 60,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
