<?php

namespace App\UseCases\Favorite;

use App\Models\User;
use App\Services\Favorite\FavoriteService;

class GetFavoritesUseCase
{
    private $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function execute(User $user)
    {
        return $this->favoriteService->getFavorites($user);
    }
}
