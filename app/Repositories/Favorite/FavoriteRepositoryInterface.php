<?php

namespace App\Repositories\Favorite;

use App\Models\User;

interface FavoriteRepositoryInterface
{
    public function findByUserAndShop(User $user, string $shopId);
    public function deleteByUserAndShop(User $user, string $shopId);
    public function getFavoritesByUser(User $user);
    public function create(array $data);
}
