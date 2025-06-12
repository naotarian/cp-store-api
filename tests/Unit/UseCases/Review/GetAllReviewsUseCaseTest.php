<?php

namespace Tests\Unit\UseCases\Review;

use Tests\TestCase;
use App\UseCases\Review\GetAllReviewsUseCase;
use App\Services\ReviewService;
use Illuminate\Support\Collection;
use Mockery;

class GetAllReviewsUseCaseTest extends TestCase
{
    private GetAllReviewsUseCase $useCase;
    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockService = Mockery::mock(ReviewService::class);
        $this->useCase = new GetAllReviewsUseCase($this->mockService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_全てのレビューを取得できる()
    {
        $expectedReviews = new Collection([
            (object) ['id' => '1', 'rating' => 4.5, 'comment' => 'Great!'],
            (object) ['id' => '2', 'rating' => 3.0, 'comment' => 'OK']
        ]);

        $this->mockService
            ->shouldReceive('getAllReviews')
            ->once()
            ->andReturn($expectedReviews);

        $result = $this->useCase->execute();

        $this->assertEquals($expectedReviews, $result);
    }
} 