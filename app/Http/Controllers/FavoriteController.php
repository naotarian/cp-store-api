<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * ユーザーのお気に入り一覧取得
     */
    public function index(): JsonResponse
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();
        $favoriteShops = $user->favoriteShops()->get();

        return response()->json([
            'status' => 'success',
            'data' => $favoriteShops
        ])->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * お気に入りに追加
     */
    public function store(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        $request->validate([
            'shop_id' => 'required|string|exists:shops,id'
        ]);

        /** @var User $user */
        $user = Auth::guard('api')->user();
        $shopId = $request->shop_id;

        // 既にお気に入りに追加済みかチェック
        if ($user->isFavoriteShop($shopId)) {
            return response()->json([
                'status' => 'error',
                'message' => '既にお気に入りに追加されています'
            ], 400)->header('Access-Control-Allow-Origin', '*');
        }

        // お気に入りに追加
        Favorite::create([
            'user_id' => $user->id,
            'shop_id' => $shopId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'お気に入りに追加しました'
        ])->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * お気に入りから削除
     */
    public function destroy($shopId): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        $user = Auth::user();

        // お気に入りを削除
        $deleted = Favorite::where('user_id', $user->id)
                          ->where('shop_id', $shopId)
                          ->delete();

        if (!$deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'お気に入りが見つかりませんでした'
            ], 404)->header('Access-Control-Allow-Origin', '*');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'お気に入りから削除しました'
        ])->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * お気に入り状態をトグル
     */
    public function toggle(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        $request->validate([
            'shop_id' => 'required|string|exists:shops,id'
        ]);

        /** @var User $user */
        $user = Auth::guard('api')->user();
        $shopId = $request->shop_id;

        if ($user->isFavoriteShop($shopId)) {
            // お気に入りから削除
            Favorite::where('user_id', $user->id)
                   ->where('shop_id', $shopId)
                   ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'お気に入りから削除しました',
                'is_favorite' => false
            ])->header('Access-Control-Allow-Origin', '*');
        } else {
            // お気に入りに追加
            Favorite::create([
                'user_id' => $user->id,
                'shop_id' => $shopId,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'お気に入りに追加しました',
                'is_favorite' => true
            ])->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * 特定の店舗のお気に入り状態を確認
     */
    public function check($shopId): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'success',
                'is_favorite' => false
            ])->header('Access-Control-Allow-Origin', '*');
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();
        $isFavorite = $user->isFavoriteShop($shopId);

        return response()->json([
            'status' => 'success',
            'is_favorite' => $isFavorite
        ])->header('Access-Control-Allow-Origin', '*');
    }
}
