<?php

namespace App\Http\Controllers\Auth\ShopAdmin;

use App\Http\Controllers\Controller;
use App\Models\ShopAdmin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * ログイン
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'password.required' => 'パスワードは必須です',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        // 店舗管理者を検索
        $shopAdmin = ShopAdmin::where('email', $request->email)
            ->where('is_active', true)
            ->with('shop')
            ->first();

        if (!$shopAdmin || !Hash::check($request->password, $shopAdmin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'メールアドレスまたはパスワードが正しくありません'
            ], 401);
        }

        // 最終ログイン時刻を更新
        $shopAdmin->updateLastLogin();

        // Sanctumトークンを生成
        $token = $shopAdmin->createToken('shop-admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $shopAdmin->id,
                'name' => $shopAdmin->name,
                'email' => $shopAdmin->email,
                'role' => $shopAdmin->role,
                'shop_id' => $shopAdmin->shop_id,
                'shop' => [
                    'id' => $shopAdmin->shop->id,
                    'name' => $shopAdmin->shop->name,
                    'slug' => $shopAdmin->shop->slug ?? null,
                ],
                'created_at' => $shopAdmin->created_at,
                'updated_at' => $shopAdmin->updated_at,
            ]
        ]);
    }

    /**
     * ログアウト
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var ShopAdmin $user */
        $user = $request->user();
        
        // 現在のトークンを削除
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'ログアウトしました'
        ]);
    }

    /**
     * 認証済みユーザー情報取得
     */
    public function me(Request $request): JsonResponse
    {
        /** @var ShopAdmin $user */
        $user = $request->user();
        $user->load('shop');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'shop_id' => $user->shop_id,
                'shop' => [
                    'id' => $user->shop->id,
                    'name' => $user->shop->name,
                    'slug' => $user->shop->slug ?? null,
                ],
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'last_login_at' => $user->last_login_at,
            ]
        ]);
    }
} 