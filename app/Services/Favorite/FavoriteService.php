<?php

namespace App\Services\Favorite;

use App\Models\User;
use App\Repositories\Favorite\FavoriteRepository;

class FavoriteService
{
    private $favoriteRepository;

    public function __construct(FavoriteRepository $favoriteRepository)
    {
        $this->favoriteRepository = $favoriteRepository;
    }

    public function getFavorites(User $user)
    {
        return $this->favoriteRepository->getFavoritesByUser($user);
    }

    public function addFavorite(User $user, string $shopId)
    {
        if ($this->isFavorite($user, $shopId)) {
            throw new \Exception('既にお気に入りに追加されています');
        }

        return $this->favoriteRepository->create([
            'user_id' => $user->id,
            'shop_id' => $shopId,
        ]);
    }

    public function removeFavorite(User $user, string $shopId)
    {
        $deleted = $this->favoriteRepository->deleteByUserAndShop($user, $shopId);
        if (!$deleted) {
            throw new \Exception('お気に入りが見つかりませんでした');
        }
        return true;
    }

    public function toggleFavorite(User $user, string $shopId)
    {
        if ($this->isFavorite($user, $shopId)) {
            $this->removeFavorite($user, $shopId);
            return false;
        } else {
            $this->addFavorite($user, $shopId);
            return true;
        }
    }

    public function isFavorite(User $user, string $shopId)
    {
        return $this->favoriteRepository->findByUserAndShop($user, $shopId) !== null;
    }
}
