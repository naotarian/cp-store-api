<?php

namespace Tests\Unit\Services\Shop;

use Tests\TestCase;
use App\Models\Shop;
use App\Services\Shop\ShopService;
use App\Repositories\Shop\ShopRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;

class ShopServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShopService $service;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ShopRepository::class);
        $this->service = new ShopService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_全ての店舗を取得できる()
    {
        // Arrange
        $shops = Shop::factory()->count(3)->create();
        
        $this->repository->shouldReceive('findAll')
            ->once()
            ->andReturn($shops);

        // Act
        $result = $this->service->getAllShops();

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_IDで店舗を取得できる()
    {
        // Arrange
        $shop = Shop::factory()->create();
        
        $this->repository->shouldReceive('findById')
            ->with($shop->id)
            ->once()
            ->andReturn($shop);

        // Act
        $result = $this->service->getShopById($shop->id);

        // Assert
        $this->assertEquals($shop->id, $result->id);
    }

    public function test_存在しない店舗を取得すると例外をスローする()
    {
        // Arrange
        $this->repository->shouldReceive('findById')
            ->with('non-existent-id')
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('店舗が見つかりませんでした');

        // Act
        $this->service->getShopById('non-existent-id');
    }

    public function test_店舗を正常に作成できる()
    {
        // Arrange
        $data = [
            'name' => 'テスト店舗',
            'description' => 'テスト用の店舗です',
            'image' => 'test-image.jpg',
            'open_time' => '09:00',
            'close_time' => '22:00',
            'address' => 'テスト住所',
            'latitude' => 35.6762,
            'longitude' => 139.6503,
        ];
        $shop = Shop::factory()->create($data);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        
        $this->repository->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($shop);

        // Act
        $result = $this->service->createShop($data);

        // Assert
        $this->assertEquals($shop->id, $result->id);
    }

    public function test_店舗作成時に例外が発生するとロールバックされる()
    {
        // Arrange
        $data = ['name' => 'test'];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        
        $this->repository->shouldReceive('create')
            ->with($data)
            ->once()
            ->andThrow(new \Exception('Database error'));

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('店舗の作成に失敗しました: Database error');

        // Act
        $this->service->createShop($data);
    }

    public function test_店舗を正常に更新できる()
    {
        // Arrange
        $shopId = 'test-id';
        $shop = Shop::factory()->create();
        $updateData = ['name' => '更新された店舗名'];
        $updatedShop = Shop::factory()->create(['name' => '更新された店舗名']);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        
        $this->repository->shouldReceive('findById')
            ->with($shopId)
            ->once()
            ->andReturn($shop);
            
        $this->repository->shouldReceive('update')
            ->with($shopId, $updateData)
            ->once()
            ->andReturn($updatedShop);

        // Act
        $result = $this->service->updateShop($shopId, $updateData);

        // Assert
        $this->assertEquals($updatedShop->id, $result->id);
    }

    public function test_存在しない店舗を更新すると例外をスローする()
    {
        // Arrange
        $shopId = 'non-existent-id';
        $updateData = ['name' => 'test'];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        
        $this->repository->shouldReceive('findById')
            ->with($shopId)
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('店舗の更新に失敗しました: 店舗が見つかりませんでした');

        // Act
        $this->service->updateShop($shopId, $updateData);
    }

    public function test_店舗を正常に削除できる()
    {
        // Arrange
        $shopId = 'test-id';
        $shop = Shop::factory()->create();

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        
        $this->repository->shouldReceive('findById')
            ->with($shopId)
            ->once()
            ->andReturn($shop);
            
        $this->repository->shouldReceive('delete')
            ->with($shopId)
            ->once()
            ->andReturn(1);

        // Act
        $result = $this->service->deleteShop($shopId);

        // Assert
        $this->assertTrue($result);
    }

    public function test_存在しない店舗を削除すると例外をスローする()
    {
        // Arrange
        $shopId = 'non-existent-id';

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        
        $this->repository->shouldReceive('findById')
            ->with($shopId)
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('店舗の削除に失敗しました: 店舗が見つかりませんでした');

        // Act
        $this->service->deleteShop($shopId);
    }

    public function test_削除処理で削除に失敗すると例外をスローする()
    {
        // Arrange
        $shopId = 'test-id';
        $shop = Shop::factory()->create();

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        
        $this->repository->shouldReceive('findById')
            ->with($shopId)
            ->once()
            ->andReturn($shop);
            
        $this->repository->shouldReceive('delete')
            ->with($shopId)
            ->once()
            ->andReturn(0);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('店舗の削除に失敗しました: 店舗の削除に失敗しました');

        // Act
        $this->service->deleteShop($shopId);
    }
} 