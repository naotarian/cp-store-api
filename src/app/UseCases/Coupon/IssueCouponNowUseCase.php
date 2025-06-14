<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Services\Coupon\CouponService;

class IssueCouponNowUseCase
{
    private $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function execute(ShopAdmin $admin, string $couponId, array $issueData)
    {
        return $this->couponService->issueCouponNow($admin, $couponId, $issueData);
    }
} 