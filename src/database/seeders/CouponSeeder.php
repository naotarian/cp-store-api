<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopAdmin;
use App\Models\Coupon;
use App\Models\CouponIssue;
use App\Models\CouponSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $shops = Shop::with('shopAdmins')->get();

        foreach ($shops as $shop) {
            $this->createCouponsForShop($shop);
        }
    }

    /**
     * 各店舗にクーポンを作成
     */
    private function createCouponsForShop(Shop $shop): void
    {
        $rootAdmin = $shop->shopAdmins()->where('role', 'root')->first();

        // 1. 定番クーポン（各店舗共通）
        $commonCoupons = $this->getCommonCoupons($shop);
        
        // 2. 店舗別特色クーポン
        $specialCoupons = $this->getSpecialCoupons($shop);

        $allCoupons = array_merge($commonCoupons, $specialCoupons);

        foreach ($allCoupons as $couponData) {
            $coupon = Coupon::create([
                'shop_id' => $shop->id,
                'title' => $couponData['title'],
                'description' => $couponData['description'],
                'conditions' => $couponData['conditions'],
                'notes' => $couponData['notes'] ?? null,
                'image_url' => $couponData['image_url'],
                'is_active' => $couponData['is_active'],
            ]);

            // 一部のクーポンは発行状態にする
            if ($couponData['should_issue'] ?? false) {
                $this->createCouponIssue($coupon, $rootAdmin);
            }

            // スケジュール設定があるクーポンはスケジュールを作成
            if (isset($couponData['schedule'])) {
                $this->createCouponSchedule($coupon, $couponData['schedule']);
            }
        }
    }

    /**
     * 共通クーポンデータを取得
     */
    private function getCommonCoupons(Shop $shop): array
    {
        return [
            [
                'title' => 'ドリンク10%OFF',
                'description' => '全ドリンクメニューが10%割引になります。',
                'conditions' => '他のクーポンとの併用不可',
                'notes' => '平日午後の空席時間に自動発行されます',
                'image_url' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&h=300&fit=crop',
                'is_active' => true,
                'should_issue' => true, // 現在発行中
                'schedule' => [
                    'name' => '平日午後の空席時間',
                    'day_type' => 'weekdays',
                    'start_time' => '14:00',
                    'end_time' => '17:00',
                    'max_acquisitions' => 20,
                ],
            ],
            [
                'title' => 'コーヒー1杯無料',
                'description' => 'ホットコーヒーまたはアイスコーヒー（Sサイズ）を1杯無料でご提供します。',
                'conditions' => '他商品と同時注文が必要',
                'notes' => 'レジでこのクーポンをご提示ください',
                'image_url' => 'https://images.unsplash.com/photo-1559496417-e7f25cb247cd?w=400&h=300&fit=crop',
                'is_active' => true,
                'should_issue' => false,
            ],
            [
                'title' => '200円割引',
                'description' => 'お会計から200円割引いたします。',
                'conditions' => '500円以上のご注文で利用可能',
                'notes' => '現金・カード決済どちらでも利用可能',
                'image_url' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=400&h=300&fit=crop',
                'is_active' => true,
                'should_issue' => true, // 現在発行中
            ],
            [
                'title' => 'スイーツ半額',
                'description' => '全スイーツメニューが半額になります。',
                'conditions' => 'ドリンクセットでご注文ください',
                'notes' => '休日ティータイムの特別サービス',
                'image_url' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=400&h=300&fit=crop',
                'is_active' => true,
                'should_issue' => false,
                'schedule' => [
                    'name' => '休日ティータイム',
                    'day_type' => 'weekends',
                    'start_time' => '15:00',
                    'end_time' => '17:00',
                    'max_acquisitions' => 15,
                ],
            ],
        ];
    }

    /**
     * 店舗別特色クーポンデータを取得
     */
    private function getSpecialCoupons(Shop $shop): array
    {
        // 店舗名に基づいた特色クーポン
        if (str_contains($shop->name, 'スターバックス')) {
            return [
                [
                    'title' => 'フラペチーノ50円OFF',
                    'description' => '全フラペチーノメニューが50円割引になります。',
                    'conditions' => null,
                    'notes' => 'スターバックス限定の特別クーポン',
                    'image_url' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400&h=300&fit=crop',
                    'is_active' => true,
                    'should_issue' => true,
                ],
            ];
        }

        if (str_contains($shop->name, 'ブルーボトル')) {
            return [
                [
                    'title' => 'スペシャリティコーヒー15%OFF',
                    'description' => 'シングルオリジンコーヒーが15%割引になります。',
                    'conditions' => '豆の購入も対象',
                    'notes' => 'こだわりのスペシャリティコーヒーをお得に',
                    'image_url' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=400&h=300&fit=crop',
                    'is_active' => true,
                    'should_issue' => false,
                ],
            ];
        }

        if (str_contains($shop->name, 'コメダ')) {
            return [
                [
                    'title' => 'シロノワール100円OFF',
                    'description' => '名物シロノワールが100円割引になります。',
                    'conditions' => 'ドリンクセットでご注文ください',
                    'notes' => 'コメダ珈琲店の名物デザート',
                    'image_url' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=400&h=300&fit=crop',
                    'is_active' => true,
                    'should_issue' => true,
                ],
            ];
        }

        // デフォルト特色クーポン
        return [
            [
                'title' => 'モーニングセット20%OFF',
                'description' => 'モーニングタイムのセットメニューが20%割引になります。',
                'conditions' => '11時までの限定メニュー',
                'notes' => '朝の時間帯限定の特別価格',
                'image_url' => 'https://images.unsplash.com/photo-1533910534207-90f31029a78e?w=400&h=300&fit=crop',
                'is_active' => true,
                'should_issue' => false,
                'schedule' => [
                    'name' => 'モーニングタイム',
                    'day_type' => 'daily',
                    'start_time' => '08:00',
                    'end_time' => '11:00',
                    'max_acquisitions' => 10,
                ],
            ],
        ];
    }

    /**
     * クーポン発行を作成
     */
    private function createCouponIssue(Coupon $coupon, ?ShopAdmin $issuer): void
    {
        $now = Carbon::now();
        $endTime = $now->copy()->addHours(rand(1, 6)); // 1時間〜6時間のランダム

        CouponIssue::create([
            'coupon_id' => $coupon->id,
            'shop_id' => $coupon->shop_id,
            'issue_type' => 'manual',
            'start_datetime' => $now,
            'end_datetime' => $endTime,
            'max_acquisitions' => rand(5, 30),
            'current_acquisitions' => rand(0, 5), // 既に何件か取得されている状態
            'status' => 'active',
            'is_active' => true,
            'issued_by' => $issuer?->id,
            'issued_at' => $now,
        ]);
    }

    /**
     * クーポンスケジュールを作成
     */
    private function createCouponSchedule(Coupon $coupon, array $scheduleData): void
    {
        $validFrom = Carbon::now()->startOfDay();

        CouponSchedule::create([
            'coupon_id' => $coupon->id,
            'shop_id' => $coupon->shop_id,
            'schedule_name' => $scheduleData['name'],
            'day_type' => $scheduleData['day_type'],
            'custom_days' => $scheduleData['custom_days'] ?? null,
            'start_time' => $scheduleData['start_time'],
            'end_time' => $scheduleData['end_time'],
            'max_acquisitions' => $scheduleData['max_acquisitions'],
            'valid_from' => $validFrom,
            'valid_until' => null, // 無期限
            'is_active' => true,
            'last_batch_processed_date' => null,
        ]);
    }


} 