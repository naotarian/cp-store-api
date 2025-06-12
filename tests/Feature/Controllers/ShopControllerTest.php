<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\Shop;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ShopControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 記事に基づく対処：setupでトランザクション開始
        $this->app->make('db')->beginTransaction();
    }

    protected function tearDown(): void
    {
        // 記事に基づく対処：tearDownでロールバック
        $this->app->make('db')->rollBack();
        parent::tearDown();
    }

    public function test_全ての店舗を正常に取得できる()
    {
        // Arrange
        $initialCount = Shop::count(); // 既存の店舗数を取得
        Shop::factory()->count(3)->create();
        $expectedCount = $initialCount + 3;

        // Act
        $response = $this->getJson('/api/shops');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ])
            ->assertJsonCount($expectedCount, 'data');
    }

    public function test_店舗詳細を正常に取得できる()
    {
        // Arrange
        $shop = Shop::factory()->create();

        // Act
        $response = $this->getJson("/api/shops/{$shop->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'description' => $shop->description,
                ]
            ]);
    }

    public function test_存在しない店舗詳細を取得すると404エラーになる()
    {
        // Act
        $response = $this->getJson('/api/shops/non-existent-id');

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => '店舗が見つかりませんでした'
            ]);
    }

    public function test_店舗を正常に作成できる()
    {
        // Arrange
        $shopData = [
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
        $response = $this->postJson('/api/shops', $shopData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => $shopData['name'],
                    'description' => $shopData['description'],
                ]
            ]);

        $this->assertDatabaseHas('shops', $shopData);
    }

    public function test_必須フィールドなしで店舗作成すると422エラーになる()
    {
        // Act
        $response = $this->postJson('/api/shops', []);

        // Assert
        $response->assertStatus(422);
    }

    public function test_店舗名なしで店舗作成すると422エラーになる()
    {
        // Arrange
        $shopData = [
            'description' => 'テスト用の店舗です',
            'image' => 'test-image.jpg',
            'open_time' => '09:00',
            'close_time' => '22:00',
            'address' => 'テスト住所',
            'latitude' => 35.6762,
            'longitude' => 139.6503,
        ];

        // Act
        $response = $this->postJson('/api/shops', $shopData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_不正な緯度で店舗作成すると422エラーになる()
    {
        // Arrange
        $shopData = [
            'name' => 'テスト店舗',
            'description' => 'テスト用の店舗です',
            'image' => 'test-image.jpg',
            'open_time' => '09:00',
            'close_time' => '22:00',
            'address' => 'テスト住所',
            'latitude' => 91.0, // 無効な緯度
            'longitude' => 139.6503,
        ];

        // Act
        $response = $this->postJson('/api/shops', $shopData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);
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
        $response = $this->putJson("/api/shops/{$shop->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $shop->id,
                    'name' => $updateData['name'],
                    'description' => $updateData['description'],
                ]
            ]);

        $this->assertDatabaseHas('shops', array_merge(['id' => $shop->id], $updateData));
    }

    public function test_存在しない店舗を更新すると400エラーになる()
    {
        // Arrange
        $updateData = [
            'name' => '更新された店舗名',
        ];

        // Act
        $response = $this->putJson('/api/shops/non-existent-id', $updateData);

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error'
            ]);
    }

    public function test_不正な緯度で店舗更新すると422エラーになる()
    {
        // Arrange
        $shop = Shop::factory()->create();
        $updateData = [
            'latitude' => 91.0, // 無効な緯度
        ];

        // Act
        $response = $this->putJson("/api/shops/{$shop->id}", $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_店舗を正常に削除できる()
    {
        // Arrange
        $shop = Shop::factory()->create();

        // Act
        $response = $this->deleteJson("/api/shops/{$shop->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Shop deleted successfully'
            ]);

        $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
    }

    public function test_存在しない店舗を削除すると400エラーになる()
    {
        // Act
        $response = $this->deleteJson('/api/shops/non-existent-id');

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error'
            ]);
    }
} 