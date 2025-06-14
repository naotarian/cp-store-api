<?php

namespace App\UseCases\Mobile\Coupon;

use App\Services\Mobile\CouponService;
use App\Exceptions\NotFoundException;

class GetShopCouponsUseCase
{
    public function __construct(
        private CouponService $couponService
    ) {}

    /**
     * 店舗のクーポン一覧を取得
     * 
     * @param string $shopId
     * @return array
     * @throws NotFoundException
     */
    public function execute(string $shopId): array
    {
        return $this->couponService->getShopCoupons($shopId);
    }
} 