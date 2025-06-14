<?php

namespace App\UseCases\Mobile\Coupon;

use App\Services\Mobile\CouponService;
use App\Exceptions\NotFoundException;

class GetActiveIssuesUseCase
{
    public function __construct(
        private CouponService $couponService
    ) {}

    /**
     * 店舗の現在発行中のクーポン一覧を取得
     * 
     * @param string $shopId
     * @return array
     * @throws NotFoundException
     */
    public function execute(string $shopId): array
    {
        return $this->couponService->getActiveIssues($shopId);
    }
} 