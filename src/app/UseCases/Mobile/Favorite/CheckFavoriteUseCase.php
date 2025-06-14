<?php

namespace App\UseCases\Mobile\Favorite;

use App\Services\Mobile\FavoriteService;

class CheckFavoriteUseCase
{
    public function __construct(
        private FavoriteService $favoriteService
    ) {}

    /**
     * お気に入り状態を確認
     * 
     * @param string $userId
     * @param string $shopId
     * @return bool
     */
    public function execute(string $userId, string $shopId): bool
    {
        return $this->favoriteService->checkFavorite($userId, $shopId);
    }
} 