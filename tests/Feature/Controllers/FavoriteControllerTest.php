<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FavoriteControllerTest extends TestCase
{
    private User $user;
    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        // 記事に基づく対処：setupでトランザクション開始
        $this->app->make('db')->beginTransaction();
        
        $this->user = User::factory()->create([
            'api_token' => hash('sha256', 'test-api-token')
        ]);
        $this->shop = Shop::factory()->create();
    }

    protected function tearDown(): void
    {
        // 記事に基づく対処：tearDownでロールバック
        $this->app->make('db')->rollBack();
        parent::tearDown();
    }

    // ヘルパーメソッド：認証ヘッダー付きリクエスト
    private function authenticatedJson($method, $uri, $data = [])
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer test-api-token',
        ])->json($method, $uri, $data);
    }

    public function test_認証なしでお気に入り一覧取得すると401エラーになる()
    {
        // Act
        $response = $this->getJson('/api/favorites');

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => '認証が必要です'
            ]);
    }

    public function test_認証ありでお気に入り一覧を正常に取得できる()
    {
        // Arrange
        Favorite::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $response = $this->authenticatedJson('GET', '/api/favorites');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_認証なしでお気に入り追加すると401エラーになる()
    {
        // Act
        $response = $this->postJson('/api/favorites', [
            'shop_id' => $this->shop->id
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => '認証が必要です'
            ]);
    }

    public function test_既にお気に入りの店舗を追加すると400エラーになる()
    {
        // Arrange
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
        ]);

        // Act
        $response = $this->authenticatedJson('POST', '/api/favorites', [
            'shop_id' => $this->shop->id
        ]);

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => '既にお気に入りに追加されています'
            ]);
    }

    public function test_お気に入りを正常に追加できる()
    {
        // Act
        $response = $this->authenticatedJson('POST', '/api/favorites', [
            'shop_id' => $this->shop->id
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'お気に入りに追加しました'
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
        ]);
    }

    public function test_認証なしでお気に入り削除すると401エラーになる()
    {
        // Act
        $response = $this->deleteJson("/api/favorites/{$this->shop->id}");

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => '認証が必要です'
            ]);
    }

    public function test_存在しないお気に入りを削除すると404エラーになる()
    {
        // Act
        $response = $this->authenticatedJson('DELETE', "/api/favorites/{$this->shop->id}");

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'お気に入りが見つかりませんでした'
            ]);
    }

    public function test_お気に入りを正常に削除できる()
    {
        // Arrange
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
        ]);

        // Act
        $response = $this->authenticatedJson('DELETE', "/api/favorites/{$this->shop->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'お気に入りから削除しました'
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
        ]);
    }

    public function test_認証なしでお気に入りトグルすると401エラーになる()
    {
        // Act
        $response = $this->postJson('/api/favorites/toggle', [
            'shop_id' => $this->shop->id
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => '認証が必要です'
            ]);
    }

    public function test_お気に入りでない店舗をトグルすると追加される()
    {
        // Act
        $response = $this->authenticatedJson('POST', '/api/favorites/toggle', [
            'shop_id' => $this->shop->id
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'お気に入りに追加しました',
                'is_favorite' => true
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
        ]);
    }

    public function test_お気に入りの店舗をトグルすると削除される()
    {
        // Arrange
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
        ]);

        // Act
        $response = $this->authenticatedJson('POST', '/api/favorites/toggle', [
            'shop_id' => $this->shop->id
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'お気に入りから削除しました',
                'is_favorite' => false
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
        ]);
    }
}
