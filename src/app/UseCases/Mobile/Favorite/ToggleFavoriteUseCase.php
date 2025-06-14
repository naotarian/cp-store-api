<?php

namespace App\UseCases\Mobile\Favorite;

use App\Services\Mobile\FavoriteService;
use App\Exceptions\NotFoundException;

class ToggleFavoriteUseCase
{
    public function __construct(
        private FavoriteService $favoriteService
    ) {}

    /**
     * お気に入り状態をトグル
     * 
     * @param string $userId
     * @param string $shopId
     * @return bool
     * @throws NotFoundException
     */
    public function execute(string $userId, string $shopId): bool
    {
        return $this->favoriteService->toggleFavorite($userId, $shopId);
    }
} 