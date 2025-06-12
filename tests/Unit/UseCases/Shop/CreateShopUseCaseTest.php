<?php

namespace Tests\Unit\UseCases\Shop;

use Tests\TestCase;
use App\Models\Shop;
use App\UseCases\Shop\CreateShopUseCase;
use App\Services\Shop\ShopService;
use Mockery;

class CreateShopUseCaseTest extends TestCase
{
    private CreateShopUseCase $useCase;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Mockery::mock(ShopService::class);
        $this->useCase = new CreateShopUseCase($this->service);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_店舗を作成できる()
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
        
        $this->service->shouldReceive('createShop')
            ->with($data)
            ->once()
            ->andReturn($shop);

        // Act
        $result = $this->useCase->execute($data);

        // Assert
        $this->assertEquals($shop->id, $result->id);
    }
} 