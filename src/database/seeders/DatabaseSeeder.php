<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // 店舗データを先に投入
        $this->call([
            ShopSeeder::class,
            ReviewSeeder::class,
            ShopAdminSeeder::class, // 店舗管理者を作成
            CouponSeeder::class,    // クーポン機能（店舗・管理者が必要なので最後）
        ]);
    }
}
