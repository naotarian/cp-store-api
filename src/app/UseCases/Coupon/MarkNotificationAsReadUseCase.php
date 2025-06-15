<?php

namespace App\UseCases\Coupon;

use App\Models\CouponAcquisition;
use App\Models\ShopAdmin;
use Exception;

class MarkNotificationAsReadUseCase
{
    /**
     * 通知を既読にする
     */
    public function execute(ShopAdmin $admin, string $acquisitionId): bool
    {
        $shopId = $admin->shop_id;
        
        // 店舗の所有する取得記録かチェック
        $acquisition = CouponAcquisition::whereHas('couponIssue.coupon', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })->find($acquisitionId);

        if (!$acquisition) {
            throw new Exception('通知が見つかりません');
        }

        // 既読フラグを追加するためのマイグレーションが必要
        // 一時的にカスタム属性で管理
        $acquisition->update([
            'is_notification_read' => true,
            'notification_read_at' => now()
        ]);

        return true;
    }
} 