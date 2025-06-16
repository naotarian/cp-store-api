<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\UseCases\Admin\GetShopInfoUseCase;
use App\UseCases\Admin\UpdateShopInfoUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    private $getShopInfoUseCase;
    private $updateShopInfoUseCase;

    public function __construct(
        GetShopInfoUseCase $getShopInfoUseCase,
        UpdateShopInfoUseCase $updateShopInfoUseCase
    ) {
        $this->getShopInfoUseCase = $getShopInfoUseCase;
        $this->updateShopInfoUseCase = $updateShopInfoUseCase;
    }

    /**
     * 店舗情報を取得
     */
    public function show(): JsonResponse
    {
        try {
            $admin = Auth::user(); // sanctumミドルウェアで既に認証済み
            $shop = $this->getShopInfoUseCase->execute($admin);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'shop' => $this->formatShopResponse($shop)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 店舗情報を更新
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
            'image' => 'nullable|url|max:500',
        ]);

        try {
            $admin = Auth::user();
            $shop = $this->updateShopInfoUseCase->execute($admin, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => '店舗情報を更新しました',
                'data' => [
                    'shop' => $this->formatShopResponse($shop)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * レスポンス用に店舗情報をフォーマット
     */
    private function formatShopResponse($shop): array
    {
        return [
            'id' => $shop->id,
            'name' => $shop->name,
            'description' => $shop->description,
            'address' => $shop->address,
            'latitude' => $shop->latitude,
            'longitude' => $shop->longitude,
            'open_time' => $shop->open_time,
            'close_time' => $shop->close_time,
            'image' => $shop->image,
            'created_at' => $shop->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $shop->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
