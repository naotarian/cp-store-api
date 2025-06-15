<?php

namespace App\UseCases\Coupon;

use App\Models\CouponAcquisition;
use App\Models\ShopAdmin;
use Exception;

class MarkBannerShownUseCase
{
    /**
     * 通知をバナー表示済みにする
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

        // バナー表示済みフラグを設定
        $acquisition->update([
            'is_banner_shown' => true,
            'banner_shown_at' => now()
        ]);

        return true;
    }
} 