<?php

namespace Tests\Unit\Repositories\Review;

use Tests\TestCase;
use App\Repositories\Review\ReviewRepository;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ReviewRepository $repository;
    private Shop $shop;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ReviewRepository();
        $this->shop = Shop::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_全てのレビューを取得できる()
    {
        // テストデータ作成
        Review::factory()->count(3)->create();

        $result = $this->repository->findAll();

        $this->assertCount(3, $result);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
    }

    public function test_IDでレビューを取得できる()
    {
        $review = Review::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'rating' => 4.5,
            'comment' => 'テストコメント'
        ]);

        $result = $this->repository->findById($review->id);

        $this->assertNotNull($result);
        $this->assertEquals($review->id, $result->id);
        $this->assertEquals(4.5, $result->rating);
        $this->assertEquals('テストコメント', $result->comment);
    }

    public function test_存在しないIDでレビューを取得するとnullが返る()
    {
        $result = $this->repository->findById('non-existent-id');

        $this->assertNull($result);
    }

    public function test_店舗IDでレビューを取得できる()
    {
        $targetShop = Shop::factory()->create();
        $otherShop = Shop::factory()->create();

        // 対象店舗のレビュー
        Review::factory()->count(2)->create(['shop_id' => $targetShop->id]);
        // 他の店舗のレビュー
        Review::factory()->count(3)->create(['shop_id' => $otherShop->id]);

        $result = $this->repository->findByShopId($targetShop->id);

        $this->assertCount(2, $result);
        foreach ($result as $review) {
            $this->assertEquals($targetShop->id, $review->shop_id);
        }
    }

    public function test_ユーザーIDでレビューを取得できる()
    {
        $targetUser = User::factory()->create();
        $otherUser = User::factory()->create();

        // 対象ユーザーのレビュー
        Review::factory()->count(2)->create(['user_id' => $targetUser->id]);
        // 他のユーザーのレビュー
        Review::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $result = $this->repository->findByUserId($targetUser->id);

        $this->assertCount(2, $result);
        foreach ($result as $review) {
            $this->assertEquals($targetUser->id, $review->user_id);
        }
    }

    public function test_店舗IDとユーザーIDでレビューを取得できる()
    {
        $review = Review::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'rating' => 4.0
        ]);

        $result = $this->repository->findByShopAndUser($this->shop->id, $this->user->id);

        $this->assertNotNull($result);
        $this->assertEquals($review->id, $result->id);
        $this->assertEquals($this->shop->id, $result->shop_id);
        $this->assertEquals($this->user->id, $result->user_id);
    }

    public function test_存在しない店舗IDとユーザーIDでレビューを取得するとnullが返る()
    {
        $result = $this->repository->findByShopAndUser('non-existent-shop', 'non-existent-user');

        $this->assertNull($result);
    }

    public function test_レビューを作成できる()
    {
        $data = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'rating' => 4.5,
            'comment' => '素晴らしいお店でした！'
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Review::class, $result);
        $this->assertEquals($this->shop->id, $result->shop_id);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals(4.5, $result->rating);
        $this->assertEquals('素晴らしいお店でした！', $result->comment);

        $this->assertDatabaseHas('reviews', $data);
    }

    public function test_レビューを更新できる()
    {
        $review = Review::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'rating' => 3.0,
            'comment' => '普通でした'
        ]);

        $updateData = [
            'rating' => 5.0,
            'comment' => '再訪して、とても良くなっていました！'
        ];

        $result = $this->repository->update($review, $updateData);

        $this->assertInstanceOf(Review::class, $result);
        $this->assertEquals(5.0, $result->rating);
        $this->assertEquals('再訪して、とても良くなっていました！', $result->comment);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5.0,
            'comment' => '再訪して、とても良くなっていました！'
        ]);
    }

    public function test_レビューを削除できる()
    {
        $review = Review::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id
        ]);

        $result = $this->repository->delete($review->id);

        $this->assertEquals(1, $result);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_存在しないIDで削除すると0が返る()
    {
        $result = $this->repository->delete('non-existent-id');

        $this->assertEquals(0, $result);
    }
} 