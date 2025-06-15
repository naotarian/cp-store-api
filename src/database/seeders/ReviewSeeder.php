<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // まずテストユーザーを作成
        $users = [
            ['name' => 'テストユーザー', 'email' => 'test@example.com'],
            ['name' => '田中太郎', 'email' => 'tanaka@example.com'],
            ['name' => '佐藤花子', 'email' => 'sato@example.com'],
            ['name' => '山田次郎', 'email' => 'yamada@example.com'],
            ['name' => '鈴木美香', 'email' => 'suzuki@example.com'],
            ['name' => '高橋健一', 'email' => 'takahashi@example.com'],
            ['name' => '伊藤さくら', 'email' => 'ito@example.com'],
            ['name' => '渡辺正夫', 'email' => 'watanabe@example.com'],
            ['name' => '小林麻衣', 'email' => 'kobayashi@example.com'],
            ['name' => '加藤大介', 'email' => 'kato@example.com'],
            ['name' => '松本優子', 'email' => 'matsumoto@example.com'],
            ['name' => '森田達也', 'email' => 'morita@example.com'],
            ['name' => '中村あい', 'email' => 'nakamura@example.com'],
            ['name' => '吉田広美', 'email' => 'yoshida@example.com'],
            ['name' => '斎藤信夫', 'email' => 'saito@example.com'],
            ['name' => '清水真理', 'email' => 'shimizu@example.com'],
            ['name' => '橋本雄一', 'email' => 'hashimoto@example.com'],
            ['name' => '藤田理恵', 'email' => 'fujita@example.com'],
            ['name' => '木村浩二', 'email' => 'kimura@example.com'],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                ]
            );
            $createdUsers[$userData['name']] = $user;
        }

        // 店舗を名前順で取得してULIDを参照
        $shops = Shop::orderBy('name')->get();
        
        if ($shops->count() < 6) {
            $this->command->warn('Not enough shops found. Please run ShopSeeder first.');
            return;
        }

        $reviewsData = [
            // スターバックス 渋谷スカイ店のレビュー (1番目の店舗)
            [
                'shop_name' => 'スターバックス 渋谷スカイ店',
                'reviews' => [
                    [
                        'user_name' => '田中太郎',
                        'rating' => 4.5,
                        'comment' => '渋谷の絶景を見ながらコーヒーを飲めるなんて最高です！少し値段は高めですが、景色代と思えば納得です。',
                    ],
                    [
                        'user_name' => '佐藤花子',
                        'rating' => 4.0,
                        'comment' => '眺めは本当に素晴らしいです。ただ、混雑していることが多いので、ゆっくりしたい時は平日がおすすめ。',
                    ],
                    [
                        'user_name' => '山田次郎',
                        'rating' => 5.0,
                        'comment' => '友人との待ち合わせで利用しました。景色もサービスも文句なしの5つ星です！',
                    ],
                ]
            ],

            // タリーズコーヒー 表参道ヒルズ店のレビュー
            [
                'shop_name' => 'タリーズコーヒー 表参道ヒルズ店',
                'reviews' => [
                    [
                        'user_name' => '鈴木美香',
                        'rating' => 4.2,
                        'comment' => '表参道の雰囲気にぴったりのオシャレな店内。季節限定ドリンクが美味しかったです。',
                    ],
                    [
                        'user_name' => '高橋健一',
                        'rating' => 3.8,
                        'comment' => 'コーヒーの質は良いですが、少し騒がしい時があります。ショッピングの合間には良いかも。',
                    ],
                    [
                        'user_name' => '伊藤さくら',
                        'rating' => 4.5,
                        'comment' => 'スタッフの対応が丁寧で好印象でした。ラテアートも綺麗で写真映えします！',
                    ],
                ]
            ],

            // ドトールコーヒーショップ 新宿南口店のレビュー
            [
                'shop_name' => 'ドトールコーヒーショップ 新宿南口店',
                'reviews' => [
                    [
                        'user_name' => '渡辺正夫',
                        'rating' => 4.0,
                        'comment' => 'コスパ最高！駅近で便利だし、朝の通勤前に立ち寄るのに最適です。',
                    ],
                    [
                        'user_name' => '小林麻衣',
                        'rating' => 3.5,
                        'comment' => '価格が安くて助かります。ただ、朝の時間帯はとても混雑するので要注意。',
                    ],
                    [
                        'user_name' => '加藤大介',
                        'rating' => 4.2,
                        'comment' => '出張で東京に来た時によく利用します。安定の美味しさで、いつでも頼りになる存在です。',
                    ],
                ]
            ],

            // ブルーボトルコーヒー 青山カフェのレビュー
            [
                'shop_name' => 'ブルーボトルコーヒー 青山カフェ',
                'reviews' => [
                    [
                        'user_name' => '松本優子',
                        'rating' => 4.8,
                        'comment' => 'コーヒーの香りと味が格別です！一杯一杯丁寧に淹れてくれるのが伝わってきます。',
                    ],
                    [
                        'user_name' => '森田達也',
                        'rating' => 4.3,
                        'comment' => 'サードウェーブコーヒーの本格的な味を楽しめます。値段は高めですが、その価値はあります。',
                    ],
                    [
                        'user_name' => '中村あい',
                        'rating' => 5.0,
                        'comment' => 'コーヒー好きなら絶対に行くべき！豆の個性を最大限に引き出した素晴らしい一杯でした。',
                    ],
                ]
            ],

            // コメダ珈琲店 銀座店のレビュー
            [
                'shop_name' => 'コメダ珈琲店 銀座店',
                'reviews' => [
                    [
                        'user_name' => '吉田広美',
                        'rating' => 4.1,
                        'comment' => 'ゆったりとした空間で、長時間の打ち合わせにも最適です。モーニングのボリュームに驚きました！',
                    ],
                    [
                        'user_name' => '斎藤信夫',
                        'rating' => 3.9,
                        'comment' => '名古屋の老舗の味を東京でも楽しめて嬉しいです。シロノワールは絶品でした。',
                    ],
                    [
                        'user_name' => '清水真理',
                        'rating' => 4.4,
                        'comment' => '友人とのんびりおしゃべりするのにぴったり。コーヒーも食事も満足できました。',
                    ],
                ]
            ],

            // 珈琲館 六本木店のレビュー
            [
                'shop_name' => '珈琲館 六本木店',
                'reviews' => [
                    [
                        'user_name' => '橋本雄一',
                        'rating' => 4.0,
                        'comment' => '落ち着いた雰囲気でビジネスミーティングに利用しました。コーヒーの質も高く、集中できる環境です。',
                    ],
                    [
                        'user_name' => '藤田理恵',
                        'rating' => 3.7,
                        'comment' => '静かで良い雰囲気ですが、少し料金が高めかな。特別な時に利用したいお店です。',
                    ],
                    [
                        'user_name' => '木村浩二',
                        'rating' => 4.3,
                        'comment' => '大人の隠れ家的な空間。デートにも使えそうな上品な雰囲気が気に入りました。',
                    ],
                ]
            ],
        ];

        // 各店舗のレビューを作成
        foreach ($reviewsData as $shopData) {
            $shop = $shops->firstWhere('name', $shopData['shop_name']);
            
            if ($shop) {
                foreach ($shopData['reviews'] as $reviewData) {
                    $user = $createdUsers[$reviewData['user_name']];
                    if ($user) {
                        // 重複チェック
                        $existingReview = Review::where('shop_id', $shop->id)
                                               ->where('user_id', $user->id)
                                               ->first();
                        
                        if (!$existingReview) {
                            Review::create([
                                'shop_id' => $shop->id,
                                'user_id' => $user->id,
                                'rating' => $reviewData['rating'],
                                'comment' => $reviewData['comment'],
                            ]);
                        }
                    }
                }
            }
        }
    }
}
