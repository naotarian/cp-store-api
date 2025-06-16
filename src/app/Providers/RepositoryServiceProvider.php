<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Favorite\FavoriteRepositoryInterface;
use App\Repositories\Favorite\FavoriteRepository;
use App\Repositories\Shop\ShopRepositoryInterface;
use App\Repositories\Shop\ShopRepository;
use App\Repositories\Review\ReviewRepositoryInterface;
use App\Repositories\Review\ReviewRepository;
use App\Repositories\Coupon\CouponRepositoryInterface;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Admin\ShopRepositoryInterface as AdminShopRepositoryInterface;
use App\Repositories\Admin\ShopRepository as AdminShopRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * リポジトリの依存性注入を登録
     */
    public function register(): void
    {
        // お気に入りリポジトリ
        $this->app->bind(FavoriteRepositoryInterface::class, FavoriteRepository::class);

        // 店舗リポジトリ
        $this->app->bind(ShopRepositoryInterface::class, ShopRepository::class);

        // レビューリポジトリ
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);

        // クーポンリポジトリ
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);

        // Admin用店舗リポジトリ
        $this->app->bind(AdminShopRepositoryInterface::class, AdminShopRepository::class);

        // 他のリポジトリの依存性注入をここに追加
    }
}
