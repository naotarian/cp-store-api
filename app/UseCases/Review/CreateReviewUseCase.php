<?php

namespace App\UseCases\Review;

use App\Services\ReviewService;
use App\Models\Review;

class CreateReviewUseCase
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function execute(array $data): Review
    {
        return $this->reviewService->createReview($data);
    }
} 