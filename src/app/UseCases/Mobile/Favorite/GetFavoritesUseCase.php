<?php

namespace App\UseCases\Mobile\Favorite;

use App\Services\Mobile\FavoriteService;

class GetFavoritesUseCase
{
    public function __construct(
        private FavoriteService $favoriteService
    ) {}

    /**
     * ユーザーのお気に入り一覧を取得
     * 
     * @param string $userId
     * @return array
     */
    public function execute(string $userId): array
    {
        return $this->favoriteService->getFavorites($userId);
    }
} 