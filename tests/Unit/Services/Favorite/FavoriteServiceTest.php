<?php

namespace Tests\Unit\Services\Favorite;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Favorite;
use App\Services\Favorite\FavoriteService;
use App\Repositories\Favorite\FavoriteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class FavoriteServiceTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteService $service;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(FavoriteRepository::class);
        $this->service = new FavoriteService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_ユーザーの全てのお気に入りを取得できる()
    {
        // Arrange
        $user = User::factory()->create();
        $shops = Shop::factory()->count(3)->create();
        $favorites = collect($shops)->map(function ($shop) use ($user) {
            return Favorite::factory()->create([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ]);
        });

        $this->repository->shouldReceive('getFavoritesByUser')
            ->with($user)
            ->once()
            ->andReturn($favorites);

        // Act
        $result = $this->service->getFavorites($user);

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_既にお気に入りに追加済みの場合は例外をスローする()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();

        $this->repository->shouldReceive('findByUserAndShop')
            ->with($user, $shop->id)
            ->once()
            ->andReturn(new Favorite());

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('既にお気に入りに追加されています');

        // Act
        $this->service->addFavorite($user, $shop->id);
    }

    public function test_お気に入りを正常に追加できる()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
        ]);

        $this->repository->shouldReceive('findByUserAndShop')
            ->with($user, $shop->id)
            ->once()
            ->andReturn(null);

        $this->repository->shouldReceive('create')
            ->with([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ])
            ->once()
            ->andReturn($favorite);

        // Act
        $result = $this->service->addFavorite($user, $shop->id);

        // Assert
        $this->assertEquals($favorite->id, $result->id);
    }

    public function test_お気に入りが見つからない場合は削除時に例外をスローする()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();

        $this->repository->shouldReceive('deleteByUserAndShop')
            ->with($user, $shop->id)
            ->once()
            ->andReturn(0);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('お気に入りが見つかりませんでした');

        // Act
        $this->service->removeFavorite($user, $shop->id);
    }

    public function test_お気に入りを正常に削除できる()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();

        $this->repository->shouldReceive('deleteByUserAndShop')
            ->with($user, $shop->id)
            ->once()
            ->andReturn(1);

        // Act
        $result = $this->service->removeFavorite($user, $shop->id);

        // Assert
        $this->assertTrue($result);
    }

    public function test_お気に入りが存在する場合はトグル時に削除される()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();

        $this->repository->shouldReceive('findByUserAndShop')
            ->with($user, $shop->id)
            ->once()
            ->andReturn(new Favorite());

        $this->repository->shouldReceive('deleteByUserAndShop')
            ->with($user, $shop->id)
            ->once()
            ->andReturn(1);

        // Act
        $result = $this->service->toggleFavorite($user, $shop->id);

        // Assert
        $this->assertFalse($result);
    }

    public function test_お気に入りが存在しない場合はトグル時に追加される()
    {
        // Arrange
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
        ]);

        $this->repository->shouldReceive('findByUserAndShop')
            ->with($user, $shop->id)
            ->twice()
            ->andReturn(null);

        $this->repository->shouldReceive('create')
            ->with([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ])
            ->once()
            ->andReturn($favorite);

        // Act
        $result = $this->service->toggleFavorite($user, $shop->id);

        // Assert
        $this->assertTrue($result);
    }
}
