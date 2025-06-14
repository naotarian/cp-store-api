<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Services\Coupon\CouponService;

class CreateCouponUseCase
{
    private $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function execute(ShopAdmin $admin, array $data)
    {
        return $this->couponService->createCoupon($admin, $data);
    }
} 