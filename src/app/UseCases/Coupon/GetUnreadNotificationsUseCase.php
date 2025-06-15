<?php

namespace App\UseCases\Coupon;

use App\Models\CouponAcquisition;
use App\Models\ShopAdmin;

class GetUnreadNotificationsUseCase
{
    /**
     * 店舗の未読クーポン取得通知のみを取得（バナー表示用）
     */
    public function execute(ShopAdmin $admin): array
    {
        $shopId = $admin->shop_id;
        
        // 24時間以内の未読クーポン取得のみを取得
        $acquisitions = CouponAcquisition::with([
            'user',
            'couponIssue.coupon'
        ])
        ->whereHas('couponIssue.coupon', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
        ->where('acquired_at', '>=', now()->subHours(24))
        ->where(function ($query) {
            $query->whereNull('is_banner_shown')
                  ->orWhere('is_banner_shown', false);
        })
        ->orderBy('acquired_at', 'desc')
        ->get();

        $notifications = $acquisitions->map(function ($acquisition) {
            return [
                'id' => $acquisition->id,
                'coupon_issue_id' => $acquisition->coupon_issue_id,
                'user_id' => $acquisition->user_id,
                'user_name' => $acquisition->user->name ?? '不明なユーザー',
                'user_avatar' => $acquisition->user->avatar_url ?? null,
                'acquired_at' => $acquisition->acquired_at->toISOString(),
                'is_read' => false, // 未読のみなので常にfalse
                // リレーション
                'coupon_issue' => [
                    'id' => $acquisition->couponIssue->id,
                    'issue_type' => $acquisition->couponIssue->issue_type,
                    'status' => $acquisition->couponIssue->status,
                    'coupon' => [
                        'id' => $acquisition->couponIssue->coupon->id,
                        'title' => $acquisition->couponIssue->coupon->title,
                        'description' => $acquisition->couponIssue->coupon->description,
                    ]
                ]
            ];
        });

        return [
            'notifications' => $notifications,
            'unread_count' => $notifications->count()
        ];
    }
} 