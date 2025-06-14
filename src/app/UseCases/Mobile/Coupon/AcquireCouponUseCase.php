<?php

namespace App\UseCases\Mobile\Coupon;

use App\Services\Mobile\CouponService;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class AcquireCouponUseCase
{
    public function __construct(
        private CouponService $couponService
    ) {}

    /**
     * クーポンを取得
     * 
     * @param string $issueId
     * @param string $userId
     * @return array
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function execute(string $issueId, string $userId): array
    {
        return $this->couponService->acquireCoupon($issueId, $userId);
    }
} 