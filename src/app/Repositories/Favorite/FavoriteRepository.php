<?php

namespace App\Repositories\Favorite;

use App\Common\RepositoryInterface;
use App\Models\Favorite;
use App\Models\User;

class FavoriteRepository implements FavoriteRepositoryInterface
{
    public function find($id)
    {
        return Favorite::find($id);
    }

    public function create(array $data)
    {
        return Favorite::create($data);
    }

    public function update($id, array $data)
    {
        $favorite = Favorite::find($id);
        if ($favorite) {
            $favorite->update($data);
            return $favorite;
        }
        return null;
    }

    public function delete($id)
    {
        return Favorite::destroy($id);
    }

    public function findByUserAndShop(User $user, string $shopId)
    {
        return Favorite::where('user_id', $user->id)
            ->where('shop_id', $shopId)
            ->first();
    }

    public function deleteByUserAndShop(User $user, string $shopId)
    {
        return Favorite::where('user_id', $user->id)
            ->where('shop_id', $shopId)
            ->delete();
    }

    public function getFavoritesByUser(User $user)
    {
        return $user->favoriteShops()->get();
    }
}
