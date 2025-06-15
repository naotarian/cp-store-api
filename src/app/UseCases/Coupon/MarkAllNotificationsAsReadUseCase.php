<?php

namespace App\UseCases\Coupon;

use App\Models\CouponAcquisition;
use App\Models\ShopAdmin;

class MarkAllNotificationsAsReadUseCase
{
    /**
     * 全ての通知を既読にする
     */
    public function execute(ShopAdmin $admin): int
    {
        $shopId = $admin->shop_id;
        
        // 24時間以内の未読通知を全て既読にする
        $updatedCount = CouponAcquisition::whereHas('couponIssue.coupon', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
        ->where('acquired_at', '>=', now()->subHours(24))
        ->where(function ($query) {
            $query->whereNull('is_notification_read')
                  ->orWhere('is_notification_read', false);
        })
        ->update([
            'is_notification_read' => true,
            'notification_read_at' => now()
        ]);

        return $updatedCount;
    }
} 