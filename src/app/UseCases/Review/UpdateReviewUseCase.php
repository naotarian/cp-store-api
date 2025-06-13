<?php

namespace App\UseCases\Review;

use App\Services\ReviewService;
use App\Models\Review;

class UpdateReviewUseCase
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function execute(string $id, array $data): Review
    {
        return $this->reviewService->updateReview($id, $data);
    }
} 