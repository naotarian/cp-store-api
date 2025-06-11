<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shop;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shops = [
            [
                'name' => 'スターバックス 渋谷スカイ店',
                'description' => '渋谷スカイの14階にある眺望抜群のスターバックス。都心の絶景を楽しみながら、厳選されたコーヒーを味わえます。',
                'image' => 'https://images.unsplash.com/photo-1559496417-e7f25cb247cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'open_time' => '08:00',
                'close_time' => '21:00',
                'address' => '東京都渋谷区渋谷2-24-12 渋谷スカイ14F',
                'latitude' => 35.659518,
                'longitude' => 139.701334,
            ],
            [
                'name' => 'タリーズコーヒー 表参道ヒルズ店',
                'description' => '表参道の洗練された空間で楽しむプレミアムコーヒー。季節限定メニューとスイーツも充実しています。',
                'image' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'open_time' => '09:00',
                'close_time' => '22:00',
                'address' => '東京都渋谷区神宮前4-12-10 表参道ヒルズ本館B3F',
                'latitude' => 35.665498,
                'longitude' => 139.709877,
            ],
            [
                'name' => 'ドトールコーヒーショップ 新宿南口店',
                'description' => '新宿駅南口から徒歩1分の好立地。リーズナブルな価格で本格的なコーヒーを提供する老舗チェーン店です。',
                'image' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'open_time' => '07:00',
                'close_time' => '23:00',
                'address' => '東京都渋谷区代々木2-7-1 小田急サザンタワー1F',
                'latitude' => 35.687574,
                'longitude' => 139.700272,
            ],
            [
                'name' => 'ブルーボトルコーヒー 青山カフェ',
                'description' => 'サードウェーブコーヒーの代表格。一杯一杯丁寧にハンドドリップで淹れる最高品質のコーヒーを提供します。',
                'image' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'open_time' => '08:00',
                'close_time' => '19:00',
                'address' => '東京都港区南青山3-13-14',
                'latitude' => 35.668251,
                'longitude' => 139.717209,
            ],
            [
                'name' => 'コメダ珈琲店 銀座店',
                'description' => '名古屋発祥の老舗珈琲店。ゆったりとした空間で、こだわりの自家焙煎コーヒーとボリューム満点のモーニングを楽しめます。',
                'image' => 'https://images.unsplash.com/photo-1521017432531-fbd92d768814?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'open_time' => '07:30',
                'close_time' => '22:30',
                'address' => '東京都中央区銀座6-3-9',
                'latitude' => 35.671267,
                'longitude' => 139.765825,
            ],
            [
                'name' => '珈琲館 六本木店',
                'description' => '落ち着いた大人の空間で、厳選された豆を使用した本格コーヒーを提供。ビジネスミーティングにも最適です。',
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'open_time' => '08:30',
                'close_time' => '21:30',
                'address' => '東京都港区六本木7-15-17',
                'latitude' => 35.662836,
                'longitude' => 139.731003,
            ],
        ];

        foreach ($shops as $shop) {
            Shop::create($shop);
        }
    }
} 