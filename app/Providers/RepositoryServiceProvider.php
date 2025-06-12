<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Favorite\FavoriteRepositoryInterface;
use App\Repositories\Favorite\FavoriteRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * リポジトリの依存性注入を登録
     */
    public function register(): void
    {
        // お気に入りリポジトリ
        $this->app->bind(FavoriteRepositoryInterface::class, FavoriteRepository::class);

        // 他のリポジトリの依存性注入をここに追加
    }
}
