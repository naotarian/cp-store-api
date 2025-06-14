<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

        // ユーザーガードでユーザーを設定
        Auth::guard('user')->setUser($user);
        
        return $next($request);
    }
}