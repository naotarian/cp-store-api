<?php

namespace App\Services\Mobile;

use App\Repositories\Mobile\FavoriteRepository;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class FavoriteService
{
    public function __construct(
        private FavoriteRepository $favoriteRepository
    ) {}

    /**
     * ユーザーのお気に入り一覧を取得
     * 
     * @param string $userId
     * @return array
     */
    public function getFavorites(string $userId): array
    {
        return $this->favoriteRepository->getFavorites($userId);
    }

    /**
     * お気に入りに追加
     * 
     * @param string $userId
     * @param string $shopId
     * @return void
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function addFavorite(string $userId, string $shopId): void
    {
        // 店舗の存在確認
        $shop = $this->favoriteRepository->findShop($shopId);
        if (!$shop) {
            throw new NotFoundException('店舗が見つかりません');
        }

        // 既にお気に入りに追加済みかチェック
        if ($this->favoriteRepository->isFavorite($userId, $shopId)) {
            throw new ValidationException('既にお気に入りに追加済みです', 400);
        }

        $this->favoriteRepository->addFavorite($userId, $shopId);
    }

    /**
     * お気に入りから削除
     * 
     * @param string $userId
     * @param string $shopId
     * @return void
     * @throws NotFoundException
     */
    public function removeFavorite(string $userId, string $shopId): void
    {
        // お気に入りに追加されているかチェック
        if (!$this->favoriteRepository->isFavorite($userId, $shopId)) {
            throw new NotFoundException('お気に入りに登録されていません');
        }

        $this->favoriteRepository->removeFavorite($userId, $shopId);
    }

    /**
     * お気に入り状態をトグル
     * 
     * @param string $userId
     * @param string $shopId
     * @return bool 追加された場合true、削除された場合false
     * @throws NotFoundException
     */
    public function toggleFavorite(string $userId, string $shopId): bool
    {
        // 店舗の存在確認
        $shop = $this->favoriteRepository->findShop($shopId);
        if (!$shop) {
            throw new NotFoundException('店舗が見つかりません');
        }

        if ($this->favoriteRepository->isFavorite($userId, $shopId)) {
            $this->favoriteRepository->removeFavorite($userId, $shopId);
            return false;
        } else {
            $this->favoriteRepository->addFavorite($userId, $shopId);
            return true;
        }
    }

    /**
     * お気に入り状態を確認
     * 
     * @param string $userId
     * @param string $shopId
     * @return bool
     */
    public function checkFavorite(string $userId, string $shopId): bool
    {
        return $this->favoriteRepository->isFavorite($userId, $shopId);
    }
} 