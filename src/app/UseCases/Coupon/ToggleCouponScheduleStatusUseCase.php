<?php

namespace App\UseCases\Coupon;

use App\Models\ShopAdmin;
use App\Repositories\Coupon\CouponScheduleRepositoryInterface;

class ToggleCouponScheduleStatusUseCase
{
    private CouponScheduleRepositoryInterface $scheduleRepository;

    public function __construct(CouponScheduleRepositoryInterface $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

    /**
     * スケジュールのステータスを切り替え
     */
    public function execute(ShopAdmin $admin, string $scheduleId)
    {
        // スケジュールを取得（権限チェック含む）
        $schedule = $this->scheduleRepository->findByIdAndShop($scheduleId, $admin->shop_id);
        
        if (!$schedule) {
            throw new \Exception('スケジュールが見つかりません');
        }

        // ステータスを切り替え
        return $this->scheduleRepository->toggleStatus($schedule);
    }
} 