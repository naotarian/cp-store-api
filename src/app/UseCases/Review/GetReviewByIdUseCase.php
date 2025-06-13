<?php

namespace App\UseCases\Review;

use App\Services\ReviewService;
use App\Models\Review;

class GetReviewByIdUseCase
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function execute(string $id): ?Review
    {
        return $this->reviewService->getReviewById($id);
    }
} 