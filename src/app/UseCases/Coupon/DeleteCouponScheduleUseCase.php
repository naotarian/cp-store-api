<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Repositories\Coupon\CouponScheduleRepositoryInterface;

class DeleteCouponScheduleUseCase
{
    private CouponScheduleRepositoryInterface $scheduleRepository;

    public function __construct(CouponScheduleRepositoryInterface $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

    /**
     * スケジュールを削除
     */
    public function execute(ShopAdmin $admin, string $scheduleId): void
    {
        // スケジュールを取得（権限チェック含む）
        $schedule = $this->scheduleRepository->findByIdAndShop($scheduleId, $admin->shop_id);
        
        if (!$schedule) {
            throw new \Exception('スケジュールが見つかりません');
        }

        // スケジュールを削除
        $this->scheduleRepository->delete($schedule);
    }
} 