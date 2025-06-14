<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\UseCases\Mobile\Favorite\GetFavoritesUseCase;
use App\UseCases\Mobile\Favorite\AddFavoriteUseCase;
use App\UseCases\Mobile\Favorite\RemoveFavoriteUseCase;
use App\UseCases\Mobile\Favorite\ToggleFavoriteUseCase;
use App\UseCases\Mobile\Favorite\CheckFavoriteUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * モバイルアプリ用お気に入りコントローラー
 * 
 * 顧客がお気に入り店舗を管理するためのAPI
 */
class FavoriteController extends Controller
{
    public function __construct(
        private GetFavoritesUseCase $getFavoritesUseCase,
        private AddFavoriteUseCase $addFavoriteUseCase,
        private RemoveFavoriteUseCase $removeFavoriteUseCase,
        private ToggleFavoriteUseCase $toggleFavoriteUseCase,
        private CheckFavoriteUseCase $checkFavoriteUseCase
    ) {}

    /**
     * ユーザーのお気に入り一覧取得
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $result = $this->getFavoritesUseCase->execute($user->id);
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * お気に入りに追加
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'shop_id' => 'required|string|exists:shops,id'
        ]);

        try {
            $userId = $request->user()->id;
            $this->addFavoriteUseCase->execute($userId, $request->shop_id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'お気に入りに追加しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * お気に入りから削除
     * 
     * @param Request $request
     * @param string $shopId
     * @return JsonResponse
     */
    public function destroy(Request $request, string $shopId): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $this->removeFavoriteUseCase->execute($userId, $shopId);
            
            return response()->json([
                'status' => 'success',
                'message' => 'お気に入りから削除しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * お気に入り状態をトグル
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'shop_id' => 'required|string|exists:shops,id'
        ]);

        try {
            $userId = $request->user()->id;
            $isFavorite = $this->toggleFavoriteUseCase->execute($userId, $request->shop_id);
            
            return response()->json([
                'status' => 'success',
                'message' => $isFavorite ? 'お気に入りに追加しました' : 'お気に入りから削除しました',
                'is_favorite' => $isFavorite
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * 特定の店舗のお気に入り状態を確認
     * 
     * @param Request $request
     * @param string $shopId
     * @return JsonResponse
     */
    public function check(Request $request, string $shopId): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $isFavorite = $this->checkFavoriteUseCase->execute($userId, $shopId);
            
            return response()->json([
                'status' => 'success',
                'is_favorite' => $isFavorite
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
} 