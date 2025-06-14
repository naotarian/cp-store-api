<?php

namespace App\Services\Coupon;

use App\Models\ShopAdmin;
use App\Repositories\Coupon\CouponRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CouponService
{
    private $couponRepository;

    public function __construct(CouponRepository $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    /**
     * 店舗の全クーポンを取得
     */
    public function getAllCoupons(ShopAdmin $admin): Collection
    {
        return $this->couponRepository->getAllCouponsByShop($admin->shop_id);
    }

    /**
     * 現在発行中のクーポンを取得
     */
    public function getActiveCouponIssues(ShopAdmin $admin): Collection
    {
        return $this->couponRepository->getActiveCouponIssuesByShop($admin->shop_id);
    }

    /**
     * スケジュール設定されたクーポンを取得
     */
    public function getCouponSchedules(ShopAdmin $admin): Collection
    {
        return $this->couponRepository->getCouponSchedulesByShop($admin->shop_id);
    }

    /**
     * クーポンを作成
     */
    public function createCoupon(ShopAdmin $admin, array $data)
    {
        // 店舗IDを設定
        $data['shop_id'] = $admin->shop_id;

        // バリデーション
        $this->validateCouponData($data);

        return $this->couponRepository->createCoupon($data);
    }

    /**
     * クーポンを更新
     */
    public function updateCoupon(ShopAdmin $admin, string $couponId, array $data)
    {
        $coupon = $this->couponRepository->findCouponById($couponId);
        
        if (!$coupon) {
            throw new \Exception('クーポンが見つかりません');
        }

        // 権限チェック
        if ($coupon->shop_id !== $admin->shop_id) {
            throw new \Exception('このクーポンを編集する権限がありません');
        }

        // バリデーション
        $this->validateCouponData($data, true);

        return $this->couponRepository->updateCoupon($couponId, $data);
    }

    /**
     * クーポンを削除
     */
    public function deleteCoupon(ShopAdmin $admin, string $couponId): bool
    {
        $coupon = $this->couponRepository->findCouponById($couponId);
        
        if (!$coupon) {
            throw new \Exception('クーポンが見つかりません');
        }

        // 権限チェック
        if ($coupon->shop_id !== $admin->shop_id) {
            throw new \Exception('このクーポンを削除する権限がありません');
        }

        // 発行中のクーポンがある場合は削除不可
        if ($coupon->activeIssues()->exists()) {
            throw new \Exception('発行中のクーポンがあるため削除できません');
        }

        return $this->couponRepository->deleteCoupon($couponId);
    }

    /**
     * クーポンを即座に発行（スポット発行）
     */
    public function issueCouponNow(ShopAdmin $admin, string $couponId, array $issueData)
    {
        $coupon = $this->couponRepository->findCouponById($couponId);
        
        if (!$coupon) {
            throw new \Exception('クーポンが見つかりません');
        }

        // 権限チェック
        if ($coupon->shop_id !== $admin->shop_id) {
            throw new \Exception('このクーポンを発行する権限がありません');
        }

        // アクティブでないクーポンは発行不可
        if (!$coupon->is_active) {
            throw new \Exception('無効なクーポンは発行できません');
        }

        // 既に発行中のクーポンがある場合は停止して新しく発行
        $existingActiveIssues = $coupon->activeIssues;
        if ($existingActiveIssues->isNotEmpty()) {
            foreach ($existingActiveIssues as $existingIssue) {
                $this->couponRepository->stopCouponIssue($existingIssue->id);
            }
        }

        $now = Carbon::now();
        $duration = $issueData['duration_minutes'] ?? 60; // デフォルト1時間

        $endTime = $now->copy()->addMinutes($duration);
        
        $issueData = array_merge($issueData, [
            'shop_id' => $admin->shop_id,
            'issue_type' => 'manual',
            'target_date' => $now->format('Y-m-d'),
            'start_time' => $now,
            'end_time' => $endTime,
            'start_time_only' => $now->format('H:i:s'),
            'end_time_only' => $endTime->format('H:i:s'),
            'status' => 'active',
            'is_active' => true,
            'issued_by' => $admin->id,
            'issued_at' => $now,
        ]);

        return $this->couponRepository->issueCoupon($couponId, $issueData);
    }

    /**
     * クーポン発行を停止
     */
    public function stopCouponIssue(ShopAdmin $admin, string $issueId): bool
    {
        $issue = $this->couponRepository->findCouponIssueById($issueId);
        
        if (!$issue) {
            throw new \Exception('クーポン発行が見つかりません');
        }

        // 権限チェック
        if ($issue->shop_id !== $admin->shop_id) {
            throw new \Exception('このクーポン発行を停止する権限がありません');
        }

        return $this->couponRepository->stopCouponIssue($issueId);
    }

    /**
     * クーポンデータのバリデーション
     */
    private function validateCouponData(array $data, bool $isUpdate = false): void
    {
        // 必須フィールドの確認（新規作成時のみ）
        if (!$isUpdate) {
            $required = ['title'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new \Exception("「{$field}」は必須項目です");
                }
            }
        }

        // タイトルの長さチェック
        if (isset($data['title']) && strlen($data['title']) > 255) {
            throw new \Exception('クーポン名は255文字以内で入力してください');
        }

        // 画像URLの長さチェック
        if (isset($data['image_url']) && strlen($data['image_url']) > 500) {
            throw new \Exception('画像URLは500文字以内で入力してください');
        }
    }

    /**
     * クーポンスケジュールを作成
     */
    public function createCouponSchedule(ShopAdmin $admin, array $data)
    {
        // 店舗IDと作成者IDを設定
        $data['shop_id'] = $admin->shop_id;
        $data['created_by'] = $admin->id;

        // 時間の順序をチェック
        $startTime = Carbon::createFromFormat('H:i', $data['start_time']);
        $endTime = Carbon::createFromFormat('H:i', $data['end_time']);
        
        if ($endTime->lte($startTime)) {
            throw new \InvalidArgumentException('終了時間は開始時間より後である必要があります');
        }

        // バリデーション
        $this->validateScheduleData($data);

        return $this->couponRepository->createCouponSchedule($data);
    }

    /**
     * 今日のスケジュールを取得
     */
    public function getTodaySchedules(ShopAdmin $admin): Collection
    {
        return $this->couponRepository->getTodaySchedulesByShop($admin->shop_id);
    }

    /**
     * スケジュールデータのバリデーション
     */
    private function validateScheduleData(array $data): void
    {
        // 基本的なバリデーション
        if (empty($data['schedule_name'])) {
            throw new \InvalidArgumentException('スケジュール名は必須です');
        }

        if (empty($data['coupon_id'])) {
            throw new \InvalidArgumentException('クーポンIDは必須です');
        }

        // カスタム曜日の場合の追加チェック
        if ($data['day_type'] === 'custom') {
            if (empty($data['custom_days']) || !is_array($data['custom_days'])) {
                throw new \InvalidArgumentException('カスタム曜日が選択されていません');
            }

            // 曜日の値が0-6の範囲内かチェック
            foreach ($data['custom_days'] as $day) {
                if (!is_int($day) || $day < 0 || $day > 6) {
                    throw new \InvalidArgumentException('無効な曜日が指定されています');
                }
            }
        }
    }
} 