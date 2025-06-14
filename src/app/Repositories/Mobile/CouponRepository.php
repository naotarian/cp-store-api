<?php

namespace App\Repositories\Mobile;

use App\Models\Shop;
use App\Models\Coupon;
use App\Models\CouponIssue;
use App\Models\CouponAcquisition;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CouponRepository
{
    /**
     * 店舗を検索
     * 
     * @param string $shopId
     * @return array|null
     */
    public function findShop(string $shopId): ?array
    {
        $shop = Shop::find($shopId);
        return $shop ? $shop->toArray() : null;
    }

    /**
     * 店舗のクーポン一覧を取得
     * 
     * @param string $shopId
     * @return array
     */
    public function getShopCoupons(string $shopId): array
    {
        return Coupon::where('shop_id', $shopId)
            ->where('is_active', true)
            ->withCount([
                'issues as active_issues_count' => function ($query) {
                    $query->where('status', 'active')
                          ->where('end_datetime', '>', now());
                },
                'schedules as schedules_count',
                'issues as total_issues_count'
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * 店舗の現在発行中のクーポン一覧を取得
     * 
     * @param string $shopId
     * @return array
     */
    public function getActiveIssues(string $shopId): array
    {
        $activeIssues = CouponIssue::with(['coupon', 'issuer'])
            ->whereHas('coupon', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId)
                      ->where('is_active', true);
            })
            ->where('status', 'active')
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>', now())
            ->get();

        return $activeIssues->map(function ($issue) {
            $endTime = Carbon::parse($issue->end_datetime);
            $now = Carbon::now();
            
            // 残り時間を計算
            $timeRemaining = $now->diffInMinutes($endTime);
            $timeRemainingText = '';
            
            if ($timeRemaining > 60) {
                $hours = floor($timeRemaining / 60);
                $minutes = $timeRemaining % 60;
                $timeRemainingText = "{$hours}時間{$minutes}分";
            } else {
                $timeRemainingText = "{$timeRemaining}分";
            }
            
            // 残り取得可能数を計算
            $remainingCount = $issue->max_acquisitions ? 
                max(0, $issue->max_acquisitions - $issue->current_acquisitions) : 
                null;
            
            // 取得可能かどうかを判定
            $isAvailable = $issue->status === 'active' && 
                           $endTime->gt($now) && 
                           ($remainingCount === null || $remainingCount > 0);
            
            return [
                'id' => $issue->id,
                'coupon_id' => $issue->coupon_id,
                'issue_type' => $issue->issue_type,
                'start_datetime' => $issue->start_datetime,
                'end_datetime' => $issue->end_datetime,
                'duration_minutes' => $issue->duration_minutes,
                'max_acquisitions' => $issue->max_acquisitions,
                'current_acquisitions' => $issue->current_acquisitions,
                'remaining_count' => $remainingCount,
                'time_remaining' => $timeRemainingText,
                'is_available' => $isAvailable,
                'status' => $issue->status,
                'issued_at' => $issue->created_at,
                'coupon' => [
                    'id' => $issue->coupon->id,
                    'title' => $issue->coupon->title,
                    'description' => $issue->coupon->description,
                    'conditions' => $issue->coupon->conditions,
                    'notes' => $issue->coupon->notes,
                ],
                'issuer' => $issue->issuer ? [
                    'id' => $issue->issuer->id,
                    'name' => $issue->issuer->name,
                ] : null,
            ];
        })->toArray();
    }

    /**
     * クーポン発行を検索
     * 
     * @param string $issueId
     * @return array|null
     */
    public function findCouponIssue(string $issueId): ?array
    {
        $issue = CouponIssue::with('coupon')->find($issueId);
        return $issue ? $issue->toArray() : null;
    }

    /**
     * ユーザーのクーポン取得を検索
     * 
     * @param string $issueId
     * @param string $userId
     * @return array|null
     */
    public function findUserCouponAcquisition(string $issueId, string $userId): ?array
    {
        $acquisition = CouponAcquisition::where('coupon_issue_id', $issueId)
            ->where('user_id', $userId)
            ->first();
        return $acquisition ? $acquisition->toArray() : null;
    }

    /**
     * ユーザーのクーポン取得をIDで検索
     * 
     * @param string $acquisitionId
     * @param string $userId
     * @return array|null
     */
    public function findUserCouponAcquisitionById(string $acquisitionId, string $userId): ?array
    {
        $acquisition = CouponAcquisition::where('id', $acquisitionId)
            ->where('user_id', $userId)
            ->first();
        return $acquisition ? $acquisition->toArray() : null;
    }

    /**
     * クーポンを取得
     * 
     * @param string $issueId
     * @param string $userId
     * @return array
     */
    public function acquireCoupon(string $issueId, string $userId): array
    {
        return DB::transaction(function () use ($issueId, $userId) {
            $issue = CouponIssue::findOrFail($issueId);
            
            // クーポンを取得
            $expiresAt = Carbon::parse($issue->end_datetime)->addDays(30); // 30日後に期限切れ
            
            $acquisition = CouponAcquisition::create([
                'coupon_issue_id' => $issueId,
                'user_id' => $userId,
                'acquired_at' => now(),
                'expired_at' => $expiresAt,
                'status' => 'active'
            ]);

            // 取得数を更新
            $issue->increment('current_acquisitions');

            return [
                'acquisition_id' => $acquisition->id,
                'expires_at' => $expiresAt->toISOString()
            ];
        });
    }

    /**
     * ユーザーの取得済みクーポン一覧を取得
     * 
     * @param string $userId
     * @return array
     */
    public function getUserCoupons(string $userId): array
    {
        $acquisitions = CouponAcquisition::with(['couponIssue.coupon.shop'])
            ->where('user_id', $userId)
            ->orderBy('acquired_at', 'desc')
            ->get();

        return $acquisitions->map(function ($acquisition) {
            // couponIssueのend_datetimeを使用期限として使用
            $couponEndDate = Carbon::parse($acquisition->couponIssue->end_datetime);
            $now = Carbon::now();
            
            $isExpired = $couponEndDate->lt($now);
            $isUsable = $acquisition->status === 'active' && !$isExpired;
            
            $timeUntilExpiry = '';
            if (!$isExpired) {
                $diffInDays = $now->diffInDays($couponEndDate);
                if ($diffInDays > 0) {
                    $timeUntilExpiry = "{$diffInDays}日後に期限切れ";
                } else {
                    $diffInHours = $now->diffInHours($couponEndDate);
                    $timeUntilExpiry = "{$diffInHours}時間後に期限切れ";
                }
            }
            
            return [
                'id' => $acquisition->id,
                'coupon_issue_id' => $acquisition->coupon_issue_id,
                'user_id' => $acquisition->user_id,
                'acquired_at' => $acquisition->acquired_at,
                'used_at' => $acquisition->used_at,
                'expired_at' => $acquisition->expired_at,
                'status' => $acquisition->status,
                'processed_by' => $acquisition->processed_by,
                'usage_notes' => $acquisition->usage_notes,
                'is_expired' => $isExpired,
                'is_usable' => $isUsable,
                'time_until_expiry' => $timeUntilExpiry,
                'coupon_issue' => [
                    'id' => $acquisition->couponIssue->id,
                    'end_datetime' => $acquisition->couponIssue->end_datetime,
                    'start_datetime' => $acquisition->couponIssue->start_datetime,
                    'duration_minutes' => $acquisition->couponIssue->duration_minutes,
                    'status' => $acquisition->couponIssue->status,
                ],
                'coupon' => [
                    'id' => $acquisition->couponIssue->coupon->id,
                    'title' => $acquisition->couponIssue->coupon->title,
                    'description' => $acquisition->couponIssue->coupon->description,
                    'conditions' => $acquisition->couponIssue->coupon->conditions,
                    'notes' => $acquisition->couponIssue->coupon->notes,
                ],
                'shop' => [
                    'id' => $acquisition->couponIssue->coupon->shop->id,
                    'name' => $acquisition->couponIssue->coupon->shop->name,
                ]
            ];
        })->toArray();
    }

    /**
     * クーポンを使用
     * 
     * @param string $acquisitionId
     * @param string $notes
     * @return void
     */
    public function useCoupon(string $acquisitionId, string $notes = ''): void
    {
        DB::transaction(function () use ($acquisitionId, $notes) {
            $acquisition = CouponAcquisition::findOrFail($acquisitionId);
            
            $acquisition->update([
                'status' => 'used',
                'used_at' => now(),
                'usage_notes' => $notes
            ]);
        });
    }
} 