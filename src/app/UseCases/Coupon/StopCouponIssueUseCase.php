<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Services\Coupon\CouponService;

class StopCouponIssueUseCase
{
    private $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function execute(ShopAdmin $admin, string $issueId): bool
    {
        return $this->couponService->stopCouponIssue($admin, $issueId);
    }
} 