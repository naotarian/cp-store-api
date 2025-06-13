<?php

namespace App\UseCases\Review;

use App\Services\ReviewService;
use Illuminate\Support\Collection;

class GetReviewsByShopIdUseCase
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function execute(string $shopId): Collection
    {
        return $this->reviewService->getReviewsByShopId($shopId);
    }
} 