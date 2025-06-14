<?php

namespace App\Repositories\Coupon;

use App\Models\CouponSchedule;
use Illuminate\Support\Collection;

interface CouponScheduleRepositoryInterface
{
    /**
     * IDと店舗IDでスケジュールを取得
     */
    public function findByIdAndShop(string $id, string $shopId): ?CouponSchedule;

    /**
     * スケジュールを作成
     */
    public function create(array $data): CouponSchedule;

    /**
     * スケジュールを更新
     */
    public function update(CouponSchedule $schedule, array $data): CouponSchedule;

    /**
     * スケジュールを削除
     */
    public function delete(CouponSchedule $schedule): bool;

    /**
     * 店舗のスケジュール一覧を取得
     */
    public function findByShopId(string $shopId): Collection;

    /**
     * スケジュールのステータスを切り替え
     */
    public function toggleStatus(CouponSchedule $schedule): CouponSchedule;
} 