<?php

namespace App\UseCases\Favorite;

use App\Models\User;
use App\Services\Favorite\FavoriteService;

class CheckFavoriteUseCase
{
    private $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function execute(User $user, string $shopId)
    {
        return $this->favoriteService->isFavorite($user, $shopId);
    }
}
