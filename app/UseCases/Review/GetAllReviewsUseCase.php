<?php

namespace App\UseCases\Review;

use App\Services\ReviewService;
use Illuminate\Support\Collection;

class GetAllReviewsUseCase
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function execute(): Collection
    {
        return $this->reviewService->getAllReviews();
    }
} 