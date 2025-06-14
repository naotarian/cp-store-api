<?php

namespace App\UseCases\Mobile\Favorite;

use App\Services\Mobile\FavoriteService;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class AddFavoriteUseCase
{
    public function __construct(
        private FavoriteService $favoriteService
    ) {}

    /**
     * お気に入りに追加
     * 
     * @param string $userId
     * @param string $shopId
     * @return void
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function execute(string $userId, string $shopId): void
    {
        $this->favoriteService->addFavorite($userId, $shopId);
    }
} 