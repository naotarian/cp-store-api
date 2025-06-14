<?php

namespace App\Repositories\Coupon;

use App\Models\Coupon;
use App\Models\CouponIssue;
use App\Models\CouponSchedule;
use Illuminate\Support\Collection;

class CouponRepository implements CouponRepositoryInterface
{
    /**
     * 店舗の全クーポンを取得
     */
    public function getAllCouponsByShop(string $shopId): Collection
    {
        return Coupon::where('shop_id', $shopId)
            ->with(['issues', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 現在発行中のクーポンを取得
     */
    public function getActiveCouponIssuesByShop(string $shopId): Collection
    {
        return CouponIssue::where('shop_id', $shopId)
            ->with(['coupon', 'shop', 'issuer'])
            ->active()
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * スケジュール設定されたクーポンを取得
     */
    public function getCouponSchedulesByShop(string $shopId): Collection
    {
        return CouponSchedule::where('shop_id', $shopId)
            ->with(['coupon', 'shop'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * クーポンを作成
     */
    public function createCoupon(array $data)
    {
        return Coupon::create($data);
    }

    /**
     * クーポンを更新
     */
    public function updateCoupon(string $id, array $data)
    {
        $coupon = Coupon::find($id);
        if ($coupon) {
            $coupon->update($data);
            return $coupon;
        }
        return null;
    }

    /**
     * クーポンを削除
     */
    public function deleteCoupon(string $id): bool
    {
        $coupon = Coupon::find($id);
        if ($coupon) {
            return $coupon->delete();
        }
        return false;
    }

    /**
     * クーポンを発行
     */
    public function issueCoupon(string $couponId, array $issueData)
    {
        $issueData['coupon_id'] = $couponId;
        return CouponIssue::create($issueData);
    }

    /**
     * クーポン発行を停止
     */
    public function stopCouponIssue(string $issueId): bool
    {
        $issue = CouponIssue::find($issueId);
        if ($issue) {
            return $issue->update([
                'status' => 'cancelled',
                'is_active' => false
            ]);
        }
        return false;
    }

    /**
     * IDでクーポンを取得
     */
    public function findCouponById(string $id)
    {
        return Coupon::with(['shop', 'issues', 'schedules'])->find($id);
    }

    /**
     * IDでクーポン発行を取得
     */
    public function findCouponIssueById(string $id)
    {
        return CouponIssue::with(['coupon', 'shop', 'issuer'])->find($id);
    }

    /**
     * クーポンスケジュールを作成
     */
    public function createCouponSchedule(array $data)
    {
        return CouponSchedule::create($data);
    }

    /**
     * 今日のスケジュールを取得
     */
    public function getTodaySchedulesByShop(string $shopId): Collection
    {
        return CouponSchedule::where('shop_id', $shopId)
            ->with(['coupon', 'creator'])
            ->todaySchedules()
            ->orderBy('start_time', 'asc')
            ->get();
    }
} 