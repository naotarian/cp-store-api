<?php

namespace App\Repositories\Coupon;

use App\Models\ShopAdmin;
use Illuminate\Support\Collection;

interface CouponRepositoryInterface
{
    /**
     * 店舗の全クーポンを取得
     */
    public function getAllCouponsByShop(string $shopId): Collection;

    /**
     * 現在発行中のクーポンを取得
     */
    public function getActiveCouponIssuesByShop(string $shopId): Collection;

    /**
     * スケジュール設定されたクーポンを取得
     */
    public function getCouponSchedulesByShop(string $shopId): Collection;

    /**
     * クーポンを作成
     */
    public function createCoupon(array $data);

    /**
     * クーポンを更新
     */
    public function updateCoupon(string $id, array $data);

    /**
     * クーポンを削除
     */
    public function deleteCoupon(string $id): bool;

    /**
     * クーポンを発行
     */
    public function issueCoupon(string $couponId, array $issueData);

    /**
     * クーポン発行を停止
     */
    public function stopCouponIssue(string $issueId): bool;

    /**
     * IDでクーポンを取得
     */
    public function findCouponById(string $id);

    /**
     * IDでクーポン発行を取得
     */
    public function findCouponIssueById(string $id);

    /**
     * クーポンスケジュールを作成
     */
    public function createCouponSchedule(array $data);

    /**
     * 今日のスケジュールを取得
     */
    public function getTodaySchedulesByShop(string $shopId): Collection;
} 