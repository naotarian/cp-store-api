<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Services\Coupon\CouponService;
use Illuminate\Support\Collection;

class GetActiveCouponIssuesUseCase
{
    private $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function execute(ShopAdmin $admin): Collection
    {
        return $this->couponService->getActiveCouponIssues($admin);
    }
} 