<?php

namespace App\Services\Mobile;

use App\Repositories\Mobile\CouponRepository;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use Carbon\Carbon;

class CouponService
{
    public function __construct(
        private CouponRepository $couponRepository
    ) {}

    /**
     * 店舗のクーポン一覧を取得
     * 
     * @param string $shopId
     * @return array
     * @throws NotFoundException
     */
    public function getShopCoupons(string $shopId): array
    {
        // 店舗の存在確認
        $shop = $this->couponRepository->findShop($shopId);
        if (!$shop) {
            throw new NotFoundException('店舗が見つかりません');
        }

        return $this->couponRepository->getShopCoupons($shopId);
    }

    /**
     * 店舗の現在発行中のクーポン一覧を取得
     * 
     * @param string $shopId
     * @return array
     * @throws NotFoundException
     */
    public function getActiveIssues(string $shopId): array
    {
        // 店舗の存在確認
        $shop = $this->couponRepository->findShop($shopId);
        if (!$shop) {
            throw new NotFoundException('店舗が見つかりません');
        }

        return $this->couponRepository->getActiveIssues($shopId);
    }

    /**
     * クーポンを取得
     * 
     * @param string $issueId
     * @param string $userId
     * @return array
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function acquireCoupon(string $issueId, string $userId): array
    {
        // クーポン発行の存在確認
        $issue = $this->couponRepository->findCouponIssue($issueId);
        if (!$issue) {
            throw new NotFoundException('クーポン発行が見つかりません');
        }

        // 取得可能かチェック
        if ($issue['status'] !== 'active') {
            throw new ValidationException('このクーポンは現在取得できません', 400);
        }

        if (Carbon::parse($issue['end_datetime'])->lt(now())) {
            throw new ValidationException('このクーポンの取得期限が過ぎています', 400);
        }

        // 既に取得済みかチェック
        $existingAcquisition = $this->couponRepository->findUserCouponAcquisition($issueId, $userId);
        if ($existingAcquisition) {
            throw new ValidationException('このクーポンは既に取得済みです', 400);
        }

        // 取得上限チェック
        if ($issue['max_acquisitions'] && $issue['current_acquisitions'] >= $issue['max_acquisitions']) {
            throw new ValidationException('このクーポンの取得上限に達しています', 400);
        }

        return $this->couponRepository->acquireCoupon($issueId, $userId);
    }

    /**
     * ユーザーの取得済みクーポン一覧を取得
     * 
     * @param string $userId
     * @return array
     */
    public function getUserCoupons(string $userId): array
    {
        return $this->couponRepository->getUserCoupons($userId);
    }

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
    public function useCoupon(string $acquisitionId, string $userId, string $notes = ''): void
    {
        // クーポン取得の存在確認
        $acquisition = $this->couponRepository->findUserCouponAcquisitionById($acquisitionId, $userId);
        if (!$acquisition) {
            throw new NotFoundException('クーポン取得が見つかりません');
        }

        if ($acquisition['status'] !== 'active') {
            throw new ValidationException('このクーポンは使用できません', 400);
        }

        if (Carbon::parse($acquisition['expired_at'])->lt(now())) {
            throw new ValidationException('このクーポンは期限切れです', 400);
        }

        $this->couponRepository->useCoupon($acquisitionId, $notes);
    }
} 