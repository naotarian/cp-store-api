<?php

namespace App\UseCases\Favorite;

use App\Models\User;
use App\Services\Favorite\FavoriteService;

class AddFavoriteUseCase
{
    private $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function execute(User $user, string $shopId)
    {
        return $this->favoriteService->addFavorite($user, $shopId);
    }
}
