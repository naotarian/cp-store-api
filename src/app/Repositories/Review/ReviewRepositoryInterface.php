<?php

namespace App\Repositories\Review;

use App\Models\Review;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    public function findAll(): Collection;
    public function findById(string $id): ?Review;
    public function findByShopId(string $shopId): Collection;
    public function findByUserId(string $userId): Collection;
    public function findByShopAndUser(string $shopId, string $userId): ?Review;
    public function create(array $data): Review;
    public function update(Review $review, array $data): Review;
    public function delete(string $id): int;
} 