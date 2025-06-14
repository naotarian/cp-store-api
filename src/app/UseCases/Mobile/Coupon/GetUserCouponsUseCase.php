<?php

namespace App\UseCases\Mobile\Coupon;

use App\Services\Mobile\CouponService;

class GetUserCouponsUseCase
{
    public function __construct(
        private CouponService $couponService
    ) {}

    /**
     * ユーザーの取得済みクーポン一覧を取得
     * 
     * @param string $userId
     * @return array
     */
    public function execute(string $userId): array
    {
        return $this->couponService->getUserCoupons($userId);
    }
} 