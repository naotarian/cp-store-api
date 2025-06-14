<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopAdmin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShopAdminSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // 既存の店舗にルート管理者を作成
        Shop::all()->each(function (Shop $shop, $index) {
            // 既にルート管理者が存在する場合はスキップ
            if ($shop->rootAdmin()->exists()) {
                return;
            }

            // 店舗名をslugに変換（英語のドメイン名用）
            $shopSlug = $this->generateShopSlug($shop->name, $index);

            // ルート管理者を作成
            ShopAdmin::create([
                'shop_id' => $shop->id,
                'name' => $shop->name . ' 管理者',
                'email' => "admin@{$shopSlug}.com",
                'password' => Hash::make('password'),
                'role' => 'root',
                'is_active' => true,
                'phone' => '090-1234-5678',
                'notes' => '店舗作成時に自動生成されたルート管理者アカウント',
            ]);
        });

        // テスト用の追加管理者を作成
        $firstShop = Shop::first();
        if ($firstShop) {
            $firstShopSlug = $this->generateShopSlug($firstShop->name, 0);

            // マネージャーを作成
            ShopAdmin::create([
                'shop_id' => $firstShop->id,
                'name' => '田中 マネージャー',
                'email' => "manager@{$firstShopSlug}.com",
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
                'phone' => '090-2345-6789',
                'notes' => 'テスト用マネージャーアカウント',
            ]);

            // スタッフを作成
            ShopAdmin::create([
                'shop_id' => $firstShop->id,
                'name' => '佐藤 スタッフ',
                'email' => "staff@{$firstShopSlug}.com",
                'password' => Hash::make('password'),
                'role' => 'staff',
                'is_active' => true,
                'phone' => '090-3456-7890',
                'notes' => 'テスト用スタッフアカウント',
            ]);
        }
    }

    /**
     * 店舗名から英語のslugを生成
     */
    private function generateShopSlug(string $shopName, int $index): string
    {
        // 店舗名に基づいた英語のslugを生成
        $slugMap = [
            'スターバックス' => 'starbucks',
            'タリーズ' => 'tullys',
            'ドトール' => 'doutor',
            'ブルーボトル' => 'bluebottle',
            'コメダ' => 'komeda',
            '珈琲館' => 'kohikan',
        ];

        foreach ($slugMap as $japanese => $english) {
            if (str_contains($shopName, $japanese)) {
                return $english . '-' . ($index + 1);
            }
        }

        // マッチしない場合はgenericなslugを生成
        return 'shop-' . ($index + 1);
    }
}
