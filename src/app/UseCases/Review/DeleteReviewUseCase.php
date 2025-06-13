<?php

namespace App\UseCases\Review;

use App\Services\ReviewService;

class DeleteReviewUseCase
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function execute(string $id): bool
    {
        return $this->reviewService->deleteReview($id);
    }
} 