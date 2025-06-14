<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        $hashedToken = hash('sha256', $token);
        $user = User::where('api_token', $hashedToken)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => '無効なトークンです'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        Log::info('ApiTokenMiddleware - User found: ' . $user->id);
        
        // ユーザーガードにユーザーを設定
        Auth::guard('user')->setUser($user);
        
        // リクエストにユーザー情報を設定（$request->user()で取得可能にする）
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}