<?php

namespace App\Services;

use App\Repositories\Review\ReviewRepositoryInterface;
use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class ReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function getAllReviews(): Collection
    {
        try {
            return $this->reviewRepository->findAll();
        } catch (Exception $e) {
            throw new Exception('レビューの取得に失敗しました: ' . $e->getMessage());
        }
    }

    public function getReviewById(string $id): ?Review
    {
        try {
            return $this->reviewRepository->findById($id);
        } catch (Exception $e) {
            throw new Exception('レビューの取得に失敗しました: ' . $e->getMessage());
        }
    }

    public function getReviewsByShopId(string $shopId): Collection
    {
        try {
            return $this->reviewRepository->findByShopId($shopId);
        } catch (Exception $e) {
            throw new Exception('店舗のレビュー取得に失敗しました: ' . $e->getMessage());
        }
    }

    public function getReviewsByUserId(string $userId): Collection
    {
        try {
            return $this->reviewRepository->findByUserId($userId);
        } catch (Exception $e) {
            throw new Exception('ユーザーのレビュー取得に失敗しました: ' . $e->getMessage());
        }
    }

    public function createReview(array $data): Review
    {
        DB::beginTransaction();
        try {
            // 重複チェック
            $existingReview = $this->reviewRepository->findByShopAndUser(
                $data['shop_id'], 
                $data['user_id']
            );

            if ($existingReview) {
                throw new Exception('この店舗にはすでにレビューを投稿されています');
            }

            $review = $this->reviewRepository->create($data);
            
            DB::commit();
            return $review;
        } catch (Exception $e) {
            DB::rollBack();
            // エラーメッセージをそのまま再スローして、Controllerで適切に処理
            throw $e;
        }
    }

    public function updateReview(string $id, array $data): Review
    {
        DB::beginTransaction();
        try {
            $review = $this->reviewRepository->findById($id);
            
            if (!$review) {
                throw new Exception('レビューが見つかりません');
            }

            $updatedReview = $this->reviewRepository->update($review, $data);
            
            DB::commit();
            return $updatedReview;
        } catch (Exception $e) {
            DB::rollBack();
            // エラーメッセージをそのまま再スローして、Controllerで適切に処理
            throw $e;
        }
    }

    public function deleteReview(string $id): bool
    {
        DB::beginTransaction();
        try {
            $review = $this->reviewRepository->findById($id);
            
            if (!$review) {
                throw new Exception('レビューが見つかりません');
            }

            $deletedCount = $this->reviewRepository->delete($id);
            
            DB::commit();
            return $deletedCount > 0;
        } catch (Exception $e) {
            DB::rollBack();
            // エラーメッセージをそのまま再スローして、Controllerで適切に処理
            throw $e;
        }
    }
} 