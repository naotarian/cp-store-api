<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * ユーザー登録
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => '名前は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に登録されています',
            'password.required' => 'パスワードは必須です',
            'password.min' => 'パスワードは6文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422)->header('Access-Control-Allow-Origin', '*');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // APIトークンを生成
        $token = $user->generateApiToken();

        return response()->json([
            'status' => 'success',
            'message' => 'ユーザー登録が完了しました',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ], 201)->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * ログイン
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'パスワードが正しくありません。',
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        /** @var User $user */
        $user = Auth::user();
        
        // APIトークンを生成
        $token = $user->generateApiToken();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'ログインしました。',
        ])->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * ログアウト
     */
    public function logout(Request $request): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            /** @var User $user */
            $user = Auth::guard('api')->user();
            // APIトークンを削除
            $user->update(['api_token' => null]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'ログアウトしました'
        ])->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * ユーザー情報取得
     */
    public function user(Request $request): JsonResponse
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();
        
        // お気に入り店舗数とレビュー数を取得
        $favoriteCount = $user->favoriteShops()->count();
        $reviewCount = $user->reviews()->count();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'favorite_count' => $favoriteCount,
                    'review_count' => $reviewCount,
                ]
            ]
        ])->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * プロフィール更新
     */
    public function updateProfile(Request $request): JsonResponse
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422)->header('Access-Control-Allow-Origin', '*');
        }

        $updateData = $request->only(['name', 'email']);
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'プロフィールを更新しました',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ]
            ]
        ])->header('Access-Control-Allow-Origin', '*');
    }
}
