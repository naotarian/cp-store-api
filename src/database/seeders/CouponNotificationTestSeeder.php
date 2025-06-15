<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shop;
use App\Models\Coupon;
use App\Models\CouponIssue;
use App\Models\CouponAcquisition;
use App\Models\User;
use App\Models\ShopAdmin;
use Carbon\Carbon;

class CouponNotificationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存の店舗とショップ管理者を取得、または作成
        $shop = Shop::first();
        if (!$shop) {
            $shop = Shop::create([
                'name' => 'テストカフェ',
                'address' => '東京都渋谷区テスト1-1-1',
                'phone' => '03-1234-5678',
                'description' => 'テスト用のカフェです',
                'is_active' => true,
            ]);
        }

        $admin = ShopAdmin::first();
        if (!$admin) {
            $admin = ShopAdmin::create([
                'shop_id' => $shop->id,
                'name' => 'テスト管理者',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
                'phone' => '03-1234-5678',
            ]);
        }

        // テストユーザーを取得または作成
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::where('email', "user{$i}@test.com")->first();
            if (!$user) {
                $user = User::create([
                    'name' => "テストユーザー{$i}",
                    'email' => "user{$i}@test.com",
                    'password' => bcrypt('password'),
                ]);
            }
            $users[] = $user;
        }

        // テストクーポンを取得または作成
        $coupon = Coupon::where('shop_id', $shop->id)
                        ->where('title', 'ドリンク10%OFF')
                        ->first();
        if (!$coupon) {
            $coupon = Coupon::create([
                'shop_id' => $shop->id,
                'title' => 'ドリンク10%OFF',
                'description' => '全ドリンクメニューが10%割引になります',
                'conditions' => '他のクーポンとの併用不可、1人1回まで',
                'notes' => 'スタッフ向けの補足情報',
                'is_active' => true,
            ]);
        }

        // クーポン発行を取得または作成
        $couponIssue = CouponIssue::where('coupon_id', $coupon->id)
                                  ->where('issue_type', 'manual')
                                  ->where('status', 'active')
                                  ->first();
        if (!$couponIssue) {
            $couponIssue = CouponIssue::create([
                'coupon_id' => $coupon->id,
                'shop_id' => $shop->id,
                'issue_type' => 'manual',
                'start_datetime' => Carbon::now()->subHours(2),
                'end_datetime' => Carbon::now()->addHours(2),
                'max_acquisitions' => 100,
                'current_acquisitions' => 0,
                'status' => 'active',
                'is_active' => true,
                'issued_by' => $admin->id,
                'issued_at' => Carbon::now()->subHours(2),
            ]);
        }

        // クーポン取得データを作成（通知用）
        foreach ($users as $index => $user) {
            // 既存の取得記録をチェック
            $existingAcquisition = CouponAcquisition::where('coupon_issue_id', $couponIssue->id)
                                                   ->where('user_id', $user->id)
                                                   ->first();
            
            if (!$existingAcquisition) {
                $isRead = $index >= 3; // 最初の3件は未読、残りは既読
                $acquiredAt = Carbon::now()->subMinutes(($index + 1) * 15); // 15分間隔で取得

                CouponAcquisition::create([
                    'coupon_issue_id' => $couponIssue->id,
                    'user_id' => $user->id,
                    'acquired_at' => $acquiredAt,
                    'expired_at' => $acquiredAt->copy()->addDays(7),
                    'status' => 'active',
                    'is_notification_read' => $isRead,
                    'notification_read_at' => $isRead ? $acquiredAt->copy()->addMinutes(5) : null,
                    'is_banner_shown' => $index >= 2, // 最初の2件はバナー未表示、残りは表示済み
                    'banner_shown_at' => $index >= 2 ? $acquiredAt->copy()->addMinutes(2) : null,
                ]);
            }
        }

        $this->command->info('クーポン取得通知のテストデータを作成しました');
        $this->command->info("- 店舗: {$shop->name}");
        $this->command->info("- 管理者: {$admin->name}");
        $this->command->info("- クーポン: {$coupon->title}");
        $this->command->info("- ユーザー: " . count($users) . "人");
        $this->command->info("- 取得通知: " . count($users) . "件（未読: 3件、既読: 2件、バナー未表示: 2件）");
    }
}
