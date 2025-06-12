<?php

namespace App\Http\Controllers;

use App\UseCases\Favorite\GetFavoritesUseCase;
use App\UseCases\Favorite\AddFavoriteUseCase;
use App\UseCases\Favorite\RemoveFavoriteUseCase;
use App\UseCases\Favorite\ToggleFavoriteUseCase;
use App\UseCases\Favorite\CheckFavoriteUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    private $getFavoritesUseCase;
    private $addFavoriteUseCase;
    private $removeFavoriteUseCase;
    private $toggleFavoriteUseCase;
    private $checkFavoriteUseCase;

    public function __construct(
        GetFavoritesUseCase $getFavoritesUseCase,
        AddFavoriteUseCase $addFavoriteUseCase,
        RemoveFavoriteUseCase $removeFavoriteUseCase,
        ToggleFavoriteUseCase $toggleFavoriteUseCase,
        CheckFavoriteUseCase $checkFavoriteUseCase
    ) {
        $this->getFavoritesUseCase = $getFavoritesUseCase;
        $this->addFavoriteUseCase = $addFavoriteUseCase;
        $this->removeFavoriteUseCase = $removeFavoriteUseCase;
        $this->toggleFavoriteUseCase = $toggleFavoriteUseCase;
        $this->checkFavoriteUseCase = $checkFavoriteUseCase;
    }

    /**
     * ユーザーのお気に入り一覧取得
     */
    public function index(): JsonResponse
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401);
        }

        $favoriteShops = $this->getFavoritesUseCase->execute(Auth::guard('api')->user());

        return response()->json([
            'status' => 'success',
            'data' => $favoriteShops
        ]);
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
            ], 401);
        }

        $request->validate([
            'shop_id' => 'required|string|exists:shops,id'
        ]);

        try {
            $this->addFavoriteUseCase->execute(Auth::guard('api')->user(), $request->shop_id);
            return response()->json([
                'status' => 'success',
                'message' => 'お気に入りに追加しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
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
            ], 401);
        }

        try {
            $this->removeFavoriteUseCase->execute(Auth::guard('api')->user(), $shopId);
            return response()->json([
                'status' => 'success',
                'message' => 'お気に入りから削除しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
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
            ], 401);
        }

        $request->validate([
            'shop_id' => 'required|string|exists:shops,id'
        ]);

        $isFavorite = $this->toggleFavoriteUseCase->execute(Auth::guard('api')->user(), $request->shop_id);

        return response()->json([
            'status' => 'success',
            'message' => $isFavorite ? 'お気に入りに追加しました' : 'お気に入りから削除しました',
            'is_favorite' => $isFavorite
        ]);
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
            ]);
        }

        $isFavorite = $this->checkFavoriteUseCase->execute(Auth::guard('api')->user(), $shopId);
        return response()->json([
            'status' => 'success',
            'is_favorite' => $isFavorite
        ]);
    }
}
