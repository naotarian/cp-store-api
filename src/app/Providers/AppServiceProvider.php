<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            \App\Repositories\Coupon\CouponRepositoryInterface::class,
            \App\Repositories\Coupon\CouponRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Coupon\CouponScheduleRepositoryInterface::class,
            \App\Repositories\Coupon\CouponScheduleRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
