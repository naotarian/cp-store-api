<?php

namespace App\UseCases\Mobile\Favorite;

use App\Services\Mobile\FavoriteService;
use App\Exceptions\NotFoundException;

class RemoveFavoriteUseCase
{
    public function __construct(
        private FavoriteService $favoriteService
    ) {}

    /**
     * お気に入りから削除
     * 
     * @param string $userId
     * @param string $shopId
     * @return void
     * @throws NotFoundException
     */
    public function execute(string $userId, string $shopId): void
    {
        $this->favoriteService->removeFavorite($userId, $shopId);
    }
} 