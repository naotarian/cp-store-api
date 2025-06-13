<?php

namespace Tests\Unit\Services\Review;

use Tests\TestCase;
use App\Services\ReviewService;
use App\Repositories\Review\ReviewRepositoryInterface;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Exception;

class ReviewServiceTest extends TestCase
{
    private ReviewService $service;
    private $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->service = new ReviewService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_全てのレビューを取得できる()
    {
        $expectedReviews = new Collection([
            Review::factory()->make(['id' => '1', 'rating' => 4.5, 'comment' => 'Good']),
            Review::factory()->make(['id' => '2', 'rating' => 3.0, 'comment' => 'OK'])
        ]);

        $this->mockRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($expectedReviews);

        $result = $this->service->getAllReviews();

        $this->assertEquals($expectedReviews, $result);
    }

    public function test_レビュー取得で例外が発生した場合はエラーメッセージ付きで再スロー()
    {
        $this->mockRepository
            ->shouldReceive('findAll')
            ->once()
            ->andThrow(new Exception('Database error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('レビューの取得に失敗しました: Database error');

        $this->service->getAllReviews();
    }

    public function test_IDでレビューを取得できる()
    {
        $reviewId = 'test-review-id';
        $expectedReview = Review::factory()->make(['id' => $reviewId, 'rating' => 4.0]);

        $this->mockRepository
            ->shouldReceive('findById')
            ->with($reviewId)
            ->once()
            ->andReturn($expectedReview);

        $result = $this->service->getReviewById($reviewId);

        $this->assertEquals($expectedReview, $result);
    }

    public function test_店舗IDでレビューを取得できる()
    {
        $shopId = 'test-shop-id';
        $expectedReviews = new Collection([
            Review::factory()->make(['id' => '1', 'shop_id' => $shopId, 'rating' => 4.5])
        ]);

        $this->mockRepository
            ->shouldReceive('findByShopId')
            ->with($shopId)
            ->once()
            ->andReturn($expectedReviews);

        $result = $this->service->getReviewsByShopId($shopId);

        $this->assertEquals($expectedReviews, $result);
    }

    public function test_ユーザーIDでレビューを取得できる()
    {
        $userId = 'test-user-id';
        $expectedReviews = new Collection([
            Review::factory()->make(['id' => '1', 'user_id' => $userId, 'rating' => 4.5])
        ]);

        $this->mockRepository
            ->shouldReceive('findByUserId')
            ->with($userId)
            ->once()
            ->andReturn($expectedReviews);

        $result = $this->service->getReviewsByUserId($userId);

        $this->assertEquals($expectedReviews, $result);
    }

    public function test_レビューを作成できる()
    {
        $reviewData = [
            'shop_id' => 'shop-1',
            'user_id' => 'user-1',
            'rating' => 4.5,
            'comment' => 'Great place!'
        ];

        $expectedReview = Review::factory()->make(array_merge($reviewData, ['id' => 'review-1']));

        // DBファサードをモック
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $this->mockRepository
            ->shouldReceive('findByShopAndUser')
            ->with('shop-1', 'user-1')
            ->once()
            ->andReturn(null); // 重複なし

        $this->mockRepository
            ->shouldReceive('create')
            ->with($reviewData)
            ->once()
            ->andReturn($expectedReview);

        $result = $this->service->createReview($reviewData);

        $this->assertEquals($expectedReview, $result);
    }

    public function test_重複レビューを作成しようとすると例外がスローされる()
    {
        $reviewData = [
            'shop_id' => 'shop-1',
            'user_id' => 'user-1',
            'rating' => 4.5,
            'comment' => 'Great place!'
        ];

        $existingReview = Review::factory()->make(['id' => 'existing-review']);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->mockRepository
            ->shouldReceive('findByShopAndUser')
            ->with('shop-1', 'user-1')
            ->once()
            ->andReturn($existingReview); // 重複あり

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('この店舗にはすでにレビューを投稿されています');

        $this->service->createReview($reviewData);
    }

    public function test_レビュー作成でリポジトリエラーが発生すると例外がスローされる()
    {
        $reviewData = [
            'shop_id' => 'shop-1',
            'user_id' => 'user-1',
            'rating' => 4.5,
            'comment' => 'Great place!'
        ];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->mockRepository
            ->shouldReceive('findByShopAndUser')
            ->with('shop-1', 'user-1')
            ->once()
            ->andReturn(null);

        $this->mockRepository
            ->shouldReceive('create')
            ->with($reviewData)
            ->once()
            ->andThrow(new Exception('Database error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->service->createReview($reviewData);
    }

    public function test_レビューを更新できる()
    {
        $reviewId = 'review-1';
        $updateData = ['rating' => 5.0, 'comment' => 'Updated comment'];
        $existingReview = Review::factory()->make(['id' => $reviewId, 'rating' => 4.0]);
        $updatedReview = Review::factory()->make(['id' => $reviewId, 'rating' => 5.0, 'comment' => 'Updated comment']);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $this->mockRepository
            ->shouldReceive('findById')
            ->with($reviewId)
            ->once()
            ->andReturn($existingReview);

        $this->mockRepository
            ->shouldReceive('update')
            ->with($existingReview, $updateData)
            ->once()
            ->andReturn($updatedReview);

        $result = $this->service->updateReview($reviewId, $updateData);

        $this->assertEquals($updatedReview, $result);
    }

    public function test_存在しないレビューを更新しようとすると例外がスローされる()
    {
        $reviewId = 'non-existent-review';
        $updateData = ['rating' => 5.0];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->mockRepository
            ->shouldReceive('findById')
            ->with($reviewId)
            ->once()
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('レビューが見つかりません');

        $this->service->updateReview($reviewId, $updateData);
    }

    public function test_レビューを削除できる()
    {
        $reviewId = 'review-1';
        $existingReview = Review::factory()->make(['id' => $reviewId]);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $this->mockRepository
            ->shouldReceive('findById')
            ->with($reviewId)
            ->once()
            ->andReturn($existingReview);

        $this->mockRepository
            ->shouldReceive('delete')
            ->with($reviewId)
            ->once()
            ->andReturn(1);

        $result = $this->service->deleteReview($reviewId);

        $this->assertTrue($result);
    }

    public function test_存在しないレビューを削除しようとすると例外がスローされる()
    {
        $reviewId = 'non-existent-review';

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->mockRepository
            ->shouldReceive('findById')
            ->with($reviewId)
            ->once()
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('レビューが見つかりません');

        $this->service->deleteReview($reviewId);
    }
} 