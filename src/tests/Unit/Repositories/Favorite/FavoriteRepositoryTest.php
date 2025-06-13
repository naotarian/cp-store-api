<?php

namespace Tests\Unit\Repositories\Favorite;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Favorite;
use App\Repositories\Favorite\FavoriteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FavoriteRepository();
    }

    public function test_ユーザーと店舗でお気に入りが存在する場合に取得できる()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
        ]);

        // Act
        $result = $this->repository->findByUserAndShop($user, $shop->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($favorite->id, $result->id);
    }

    public function test_ユーザーと店舗でお気に入りが存在しない場合はnullを返す()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();

        // Act
        $result = $this->repository->findByUserAndShop($user, $shop->id);

        // Assert
        $this->assertNull($result);
    }

    public function test_ユーザーの全てのお気に入りを取得できる()
    {
        // Arrange
        $user = User::factory()->create();
        $shops = Shop::factory()->count(3)->create();
        $favorites = $shops->map(function ($shop) use ($user) {
            return Favorite::factory()->create([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ]);
        });

        // Act
        $result = $this->repository->getFavoritesByUser($user);

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_お気に入りを正常に作成できる()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $data = [
            'user_id' => $user->id,
            'shop_id' => $shop->id,
        ];

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($shop->id, $result->shop_id);
        $this->assertDatabaseHas('favorites', $data);
    }

    public function test_ユーザーと店舗でお気に入りを削除すると成功する()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
        ]);

        // Act
        $result = $this->repository->deleteByUserAndShop($user, $shop->id);

        // Assert
        $this->assertEquals(1, $result);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'shop_id' => $shop->id,
        ]);
    }
}
