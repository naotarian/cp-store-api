<?php

namespace App\UseCases\Mobile\Coupon;

use App\Services\Mobile\CouponService;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class UseCouponUseCase
{
    public function __construct(
        private CouponService $couponService
    ) {}

    /**
     * クーポンを使用
     * 
     * @param string $acquisitionId
     * @param string $userId
     * @param string $notes
     * @return void
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function execute(string $acquisitionId, string $userId, string $notes = ''): void
    {
        $this->couponService->useCoupon($acquisitionId, $userId, $notes);
    }
} 