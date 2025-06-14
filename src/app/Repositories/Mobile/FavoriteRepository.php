<?php

namespace App\Repositories\Mobile;

use App\Models\Shop;
use App\Models\Favorite;
use Illuminate\Support\Facades\DB;

class FavoriteRepository
{
    /**
     * 店舗を検索
     * 
     * @param string $shopId
     * @return array|null
     */
    public function findShop(string $shopId): ?array
    {
        $shop = Shop::find($shopId);
        return $shop ? $shop->toArray() : null;
    }

    /**
     * ユーザーのお気に入り一覧を取得
     * 
     * @param string $userId
     * @return array
     */
    public function getFavorites(string $userId): array
    {
        $favorites = Favorite::with(['shop'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $favorites->map(function ($favorite) {
            return [
                'id' => $favorite->id,
                'user_id' => $favorite->user_id,
                'shop_id' => $favorite->shop_id,
                'created_at' => $favorite->created_at,
                'shop' => [
                    'id' => $favorite->shop->id,
                    'name' => $favorite->shop->name,
                    'description' => $favorite->shop->description,
                    'address' => $favorite->shop->address,
                    'phone' => $favorite->shop->phone,
                    'open_time' => $favorite->shop->open_time,
                    'close_time' => $favorite->shop->close_time,
                    'image' => $favorite->shop->image,
                    'average_rating' => $favorite->shop->average_rating,
                    'review_count' => $favorite->shop->review_count,
                ]
            ];
        })->toArray();
    }

    /**
     * お気に入り状態を確認
     * 
     * @param string $userId
     * @param string $shopId
     * @return bool
     */
    public function isFavorite(string $userId, string $shopId): bool
    {
        return Favorite::where('user_id', $userId)
            ->where('shop_id', $shopId)
            ->exists();
    }

    /**
     * お気に入りに追加
     * 
     * @param string $userId
     * @param string $shopId
     * @return void
     */
    public function addFavorite(string $userId, string $shopId): void
    {
        DB::transaction(function () use ($userId, $shopId) {
            Favorite::create([
                'user_id' => $userId,
                'shop_id' => $shopId
            ]);
        });
    }

    /**
     * お気に入りから削除
     * 
     * @param string $userId
     * @param string $shopId
     * @return void
     */
    public function removeFavorite(string $userId, string $shopId): void
    {
        DB::transaction(function () use ($userId, $shopId) {
            Favorite::where('user_id', $userId)
                ->where('shop_id', $shopId)
                ->delete();
        });
    }
} 