<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Repositories\Coupon\CouponScheduleRepositoryInterface;

class UpdateCouponScheduleUseCase
{
    private CouponScheduleRepositoryInterface $scheduleRepository;

    public function __construct(CouponScheduleRepositoryInterface $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

    /**
     * スケジュールを更新
     */
    public function execute(ShopAdmin $admin, string $scheduleId, array $data)
    {
        // スケジュールを取得（権限チェック含む）
        $schedule = $this->scheduleRepository->findByIdAndShop($scheduleId, $admin->shop_id);
        
        if (!$schedule) {
            throw new \Exception('スケジュールが見つかりません');
        }

        // スケジュールを更新
        return $this->scheduleRepository->update($schedule, $data);
    }
} 