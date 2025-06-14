<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Services\Coupon\CouponService;

class UpdateCouponUseCase
{
    private $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function execute(ShopAdmin $admin, string $couponId, array $data)
    {
        return $this->couponService->updateCoupon($admin, $couponId, $data);
    }
} 