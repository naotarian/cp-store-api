<?php

namespace Tests\Unit\UseCases\Shop;

use Tests\TestCase;
use App\Models\Shop;
use App\UseCases\Shop\GetAllShopsUseCase;
use App\Services\Shop\ShopService;
use Mockery;

class GetAllShopsUseCaseTest extends TestCase
{
    private GetAllShopsUseCase $useCase;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Mockery::mock(ShopService::class);
        $this->useCase = new GetAllShopsUseCase($this->service);
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
        
        $this->service->shouldReceive('getAllShops')
            ->once()
            ->andReturn($shops);

        // Act
        $result = $this->useCase->execute();

        // Assert
        $this->assertCount(3, $result);
    }
} 