<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Services\Coupon\CouponService;

class CreateCouponScheduleUseCase
{
    private $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function execute(ShopAdmin $admin, array $scheduleData)
    {
        return $this->couponService->createCouponSchedule($admin, $scheduleData);
    }
} 