<?php

namespace Tests\Unit\UseCases\Review;

use Tests\TestCase;
use App\UseCases\Review\CreateReviewUseCase;
use App\Services\ReviewService;
use App\Models\Review;
use Mockery;

class CreateReviewUseCaseTest extends TestCase
{
    private CreateReviewUseCase $useCase;
    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockService = Mockery::mock(ReviewService::class);
        $this->useCase = new CreateReviewUseCase($this->mockService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_レビューを作成できる()
    {
        $reviewData = [
            'shop_id' => 'shop-1',
            'user_id' => 'user-1',
            'rating' => 4.5,
            'comment' => '素晴らしいお店でした！'
        ];

        $expectedReview = Review::factory()->make(array_merge($reviewData, ['id' => 'review-1']));

        $this->mockService
            ->shouldReceive('createReview')
            ->with($reviewData)
            ->once()
            ->andReturn($expectedReview);

        $result = $this->useCase->execute($reviewData);

        $this->assertEquals($expectedReview, $result);
    }
} 