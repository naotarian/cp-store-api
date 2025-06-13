<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewControllerTest extends TestCase
{
    private User $user;
    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        // トランザクション開始
        $this->app->make('db')->beginTransaction();
        
        $this->user = User::factory()->create([
            'api_token' => hash('sha256', 'test-api-token')
        ]);
        $this->shop = Shop::factory()->create();
    }

    protected function tearDown(): void
    {
        // ロールバック
        $this->app->make('db')->rollBack();
        parent::tearDown();
    }

    // ヘルパーメソッド：認証ヘッダー付きリクエスト
    private function authenticatedRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->json($method, $uri, $data, [
            'Authorization' => 'Bearer test-api-token'
        ]);
    }

    public function test_認証なしでレビュー一覧取得すると正常に取得できる()
    {
        // レビューを作成
        Review::factory()->count(3)->create(['shop_id' => $this->shop->id]);

        $response = $this->json('GET', '/api/reviews');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'id',
                            'shop_id',
                            'user_id',
                            'rating',
                            'comment',
                            'created_at',
                            'updated_at',
                            'shop',
                            'user'
                        ]
                    ]
                ]);
    }

    public function test_存在するレビュー詳細を正常に取得できる()
    {
        $review = Review::factory()->create(['shop_id' => $this->shop->id]);

        $response = $this->json('GET', "/api/reviews/{$review->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id',
                        'shop_id',
                        'user_id',
                        'rating',
                        'comment',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'id' => $review->id
                    ]
                ]);
    }

    public function test_存在しないレビュー詳細を取得すると404エラーになる()
    {
        $response = $this->json('GET', '/api/reviews/non-existent-id');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'レビューが見つかりません'
                ]);
    }

    public function test_店舗のレビューを正常に取得できる()
    {
        // この店舗のレビューを3件作成
        Review::factory()->count(3)->create(['shop_id' => $this->shop->id]);
        // 他の店舗のレビューも作成
        $otherShop = Shop::factory()->create();
        Review::factory()->count(2)->create(['shop_id' => $otherShop->id]);

        $response = $this->json('GET', "/api/shops/{$this->shop->id}/reviews");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'id',
                            'shop_id',
                            'user_id',
                            'rating',
                            'comment',
                            'created_at',
                            'updated_at',
                            'user'
                        ]
                    ]
                ]);

        // この店舗のレビューのみが返されることを確認
        $data = $response->json('data');
        foreach ($data as $review) {
            $this->assertEquals($this->shop->id, $review['shop_id']);
        }
    }

    public function test_認証なしでレビュー投稿すると401エラーになる()
    {
        $response = $this->json('POST', '/api/reviews', [
            'shop_id' => $this->shop->id,
            'rating' => 4.5,
            'comment' => 'とても良いお店でした！'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'status' => 'error',
                    'message' => '認証が必要です'
                ]);
    }

    public function test_レビューを正常に投稿できる()
    {
        $response = $this->authenticatedRequest('POST', '/api/reviews', [
            'shop_id' => $this->shop->id,
            'rating' => 4.5,
            'comment' => 'とても良いお店でした！'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'shop_id',
                        'user_id',
                        'rating',
                        'comment',
                        'created_at',
                        'updated_at',
                        'user'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'レビューを投稿しました',
                    'data' => [
                        'shop_id' => $this->shop->id,
                        'user_id' => $this->user->id,
                        'rating' => 4.5,
                        'comment' => 'とても良いお店でした！'
                    ]
                ]);

        $this->assertDatabaseHas('reviews', [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'rating' => 4.5,
            'comment' => 'とても良いお店でした！'
        ]);
    }

    public function test_必須フィールドなしでレビュー投稿すると422エラーになる()
    {
        $response = $this->authenticatedRequest('POST', '/api/reviews', []);

        $response->assertStatus(422);
    }

    public function test_存在しない店舗にレビュー投稿すると422エラーになる()
    {
        $response = $this->authenticatedRequest('POST', '/api/reviews', [
            'shop_id' => 'non-existent-shop-id',
            'rating' => 4.5,
            'comment' => 'コメント'
        ]);

        $response->assertStatus(422);
    }

    public function test_不正な評価でレビュー投稿すると422エラーになる()
    {
        $response = $this->authenticatedRequest('POST', '/api/reviews', [
            'shop_id' => $this->shop->id,
            'rating' => 6, // 1-5の範囲外
            'comment' => 'コメント'
        ]);

        $response->assertStatus(422);

        $response = $this->authenticatedRequest('POST', '/api/reviews', [
            'shop_id' => $this->shop->id,
            'rating' => 0, // 1-5の範囲外
            'comment' => 'コメント'
        ]);

        $response->assertStatus(422);
    }

    public function test_既にレビューした店舗に再度レビュー投稿すると409エラーになる()
    {
        // 最初のレビューを投稿
        $this->authenticatedRequest('POST', '/api/reviews', [
            'shop_id' => $this->shop->id,
            'rating' => 4.0,
            'comment' => '最初のレビュー'
        ]);

        // 同じ店舗に再度レビューを投稿
        $response = $this->authenticatedRequest('POST', '/api/reviews', [
            'shop_id' => $this->shop->id,
            'rating' => 5.0,
            'comment' => '2回目のレビュー'
        ]);

        $response->assertStatus(409)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'この店舗にはすでにレビューを投稿されています'
                ]);
    }

    public function test_認証なしでレビュー更新すると401エラーになる()
    {
        $review = Review::factory()->create(['shop_id' => $this->shop->id]);

        $response = $this->json('PUT', "/api/reviews/{$review->id}", [
            'rating' => 3.0,
            'comment' => '更新されたコメント'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'status' => 'error',
                    'message' => '認証が必要です'
                ]);
    }

    public function test_レビューを正常に更新できる()
    {
        $review = Review::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->authenticatedRequest('PUT', "/api/reviews/{$review->id}", [
            'rating' => 3.0,
            'comment' => '更新されたコメント'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'レビューを更新しました'
                ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3.0,
            'comment' => '更新されたコメント'
        ]);
    }

    public function test_存在しないレビューを更新すると404エラーになる()
    {
        $response = $this->authenticatedRequest('PUT', '/api/reviews/non-existent-id', [
            'rating' => 3.0,
            'comment' => '更新されたコメント'
        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'レビューが見つかりません'
                ]);
    }

    public function test_認証なしでレビュー削除すると401エラーになる()
    {
        $review = Review::factory()->create(['shop_id' => $this->shop->id]);

        $response = $this->json('DELETE', "/api/reviews/{$review->id}");

        $response->assertStatus(401)
                ->assertJson([
                    'status' => 'error',
                    'message' => '認証が必要です'
                ]);
    }

    public function test_レビューを正常に削除できる()
    {
        $review = Review::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->authenticatedRequest('DELETE', "/api/reviews/{$review->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'レビューを削除しました'
                ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id
        ]);
    }

    public function test_存在しないレビューを削除すると404エラーになる()
    {
        $response = $this->authenticatedRequest('DELETE', '/api/reviews/non-existent-id');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'レビューが見つかりません'
                ]);
    }
} 