<?php

namespace Tests\Unit\Repositories\Shop;

use Tests\TestCase;
use App\Models\Shop;
use App\Repositories\Shop\ShopRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShopRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ShopRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ShopRepository();
    }

    public function test_全ての店舗を取得できる()
    {
        // Arrange
        Shop::factory()->count(3)->create();

        // Act
        $result = $this->repository->findAll();

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_IDで店舗を取得できる()
    {
        // Arrange
        $shop = Shop::factory()->create();

        // Act
        $result = $this->repository->findById($shop->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($shop->id, $result->id);
        $this->assertEquals($shop->name, $result->name);
    }

    public function test_存在しないIDで店舗を取得するとnullを返す()
    {
        // Act
        $result = $this->repository->findById('non-existent-id');

        // Assert
        $this->assertNull($result);
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

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($data['name'], $result->name);
        $this->assertEquals($data['description'], $result->description);
        $this->assertDatabaseHas('shops', $data);
    }

    public function test_店舗を正常に更新できる()
    {
        // Arrange
        $shop = Shop::factory()->create();
        $updateData = [
            'name' => '更新された店舗名',
            'description' => '更新された説明',
        ];

        // Act
        $result = $this->repository->update($shop->id, $updateData);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($updateData['name'], $result->name);
        $this->assertEquals($updateData['description'], $result->description);
        $this->assertDatabaseHas('shops', array_merge(['id' => $shop->id], $updateData));
    }

    public function test_存在しない店舗を更新するとnullを返す()
    {
        // Act
        $result = $this->repository->update('non-existent-id', ['name' => 'test']);

        // Assert
        $this->assertNull($result);
    }

    public function test_店舗を正常に削除できる()
    {
        // Arrange
        $shop = Shop::factory()->create();

        // Act
        $result = $this->repository->delete($shop->id);

        // Assert
        $this->assertEquals(1, $result);
        $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
    }

    public function test_存在しない店舗を削除すると0を返す()
    {
        // Act
        $result = $this->repository->delete('non-existent-id');

        // Assert
        $this->assertEquals(0, $result);
    }
} 