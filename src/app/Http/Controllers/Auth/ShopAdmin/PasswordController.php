<?php

namespace App\Http\Controllers\Auth\ShopAdmin;

use App\Http\Controllers\Controller;
use App\Models\ShopAdmin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PasswordController extends Controller
{
    /**
     * パスワードリセットメール送信
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:shop_admins,email',
        ], [
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.exists' => 'このメールアドレスは登録されていません',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $shopAdmin = ShopAdmin::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$shopAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'アクティブなアカウントが見つかりません'
            ], 404);
        }

        // パスワードリセットトークンを生成
        $token = Str::random(64);
        
        // password_reset_tokensテーブルに保存
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // TODO: 実際のメール送信処理を実装
        // 現在は開発用にログに出力
        Log::info('Password reset token for shop admin', [
            'email' => $request->email,
            'token' => $token,
            'reset_url' => config('app.frontend_url') . '/reset-password?token=' . $token
        ]);

        return response()->json([
            'success' => true,
            'message' => 'パスワードリセット用のメールを送信しました。メールをご確認ください。'
        ]);
    }

    /**
     * パスワードリセット実行
     */
    public function reset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:shop_admins,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'token.required' => 'リセットトークンは必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.exists' => 'このメールアドレスは登録されていません',
            'password.required' => 'パスワードは必須です',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // トークンの検証
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'success' => false,
                'message' => '無効なリセットトークンです'
            ], 400);
        }

        // トークンの有効期限チェック（24時間）
        if (now()->diffInHours($passwordReset->created_at) > 24) {
            return response()->json([
                'success' => false,
                'message' => 'リセットトークンの有効期限が切れています'
            ], 400);
        }

        // 店舗管理者を取得
        $shopAdmin = ShopAdmin::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$shopAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'アクティブなアカウントが見つかりません'
            ], 404);
        }

        // パスワードを更新
        $shopAdmin->update([
            'password' => Hash::make($request->password)
        ]);

        // 使用済みトークンを削除
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'パスワードが正常に更新されました'
        ]);
    }

    /**
     * トークンの有効性確認
     */
    public function validateToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'success' => false,
                'message' => '無効なリセットトークンです'
            ], 400);
        }

        // トークンの有効期限チェック（24時間）
        if (now()->diffInHours($passwordReset->created_at) > 24) {
            return response()->json([
                'success' => false,
                'message' => 'リセットトークンの有効期限が切れています'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'トークンは有効です'
        ]);
    }
} 