<?php

namespace App\UseCases\Coupon;

use App\Models\CouponAcquisition;
use App\Models\ShopAdmin;
use Illuminate\Database\Eloquent\Collection;

class GetAcquisitionNotificationsUseCase
{
    /**
     * 店舗のクーポン取得通知を取得
     */
    public function execute(ShopAdmin $admin): array
    {
        $shopId = $admin->shop_id;
        
        // 24時間以内のクーポン取得を通知として取得
        // 通知一覧では全ての通知を表示（既読・未読両方）
        $acquisitions = CouponAcquisition::with([
            'user',
            'couponIssue.coupon'
        ])
        ->whereHas('couponIssue.coupon', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
        ->where('acquired_at', '>=', now()->subHours(24))
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
                'is_read' => $acquisition->is_notification_read ?? false,
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

        // 未読数をカウント（nullも未読として扱う）
        $unreadCount = $acquisitions->filter(function ($acquisition) {
            return !$acquisition->is_notification_read;
        })->count();

        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ];
    }
} 