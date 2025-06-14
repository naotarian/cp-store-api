<?php

namespace App\Repositories\Coupon;

use App\Models\CouponSchedule;
use Illuminate\Support\Collection;

class CouponScheduleRepository implements CouponScheduleRepositoryInterface
{
    /**
     * IDと店舗IDでスケジュールを取得
     */
    public function findByIdAndShop(string $id, string $shopId): ?CouponSchedule
    {
        return CouponSchedule::where('id', $id)
            ->where('shop_id', $shopId)
            ->first();
    }

    /**
     * スケジュールを作成
     */
    public function create(array $data): CouponSchedule
    {
        return CouponSchedule::create($data);
    }

    /**
     * スケジュールを更新
     */
    public function update(CouponSchedule $schedule, array $data): CouponSchedule
    {
        $schedule->update($data);
        return $schedule->fresh();
    }

    /**
     * スケジュールを削除
     */
    public function delete(CouponSchedule $schedule): bool
    {
        return $schedule->delete();
    }

    /**
     * 店舗のスケジュール一覧を取得
     */
    public function findByShopId(string $shopId): Collection
    {
        return CouponSchedule::where('shop_id', $shopId)
            ->with(['coupon'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * スケジュールのステータスを切り替え
     */
    public function toggleStatus(CouponSchedule $schedule): CouponSchedule
    {
        $schedule->update([
            'is_active' => !$schedule->is_active
        ]);
        return $schedule->fresh();
    }
} 