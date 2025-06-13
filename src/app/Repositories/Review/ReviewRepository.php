<?php

namespace App\Repositories\Review;

use App\Models\Review;
use Illuminate\Support\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function findAll(): Collection
    {
        return Review::with(['shop', 'user'])->get();
    }

    public function findById(string $id): ?Review
    {
        return Review::with(['shop', 'user'])->find($id);
    }

    public function findByShopId(string $shopId): Collection
    {
        return Review::with('user:id,name')
                    ->where('shop_id', $shopId)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    public function findByUserId(string $userId): Collection
    {
        return Review::with('shop')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    public function findByShopAndUser(string $shopId, string $userId): ?Review
    {
        return Review::where('shop_id', $shopId)
                    ->where('user_id', $userId)
                    ->first();
    }

    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function update(Review $review, array $data): Review
    {
        $review->update($data);
        return $review->fresh();
    }

    public function delete(string $id): int
    {
        return Review::destroy($id);
    }
} 