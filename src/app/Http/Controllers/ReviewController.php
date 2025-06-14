<?php

namespace App\Http\Controllers;

use App\UseCases\Review\GetAllReviewsUseCase;
use App\UseCases\Review\GetReviewByIdUseCase;
use App\UseCases\Review\GetReviewsByShopIdUseCase;
use App\UseCases\Review\CreateReviewUseCase;
use App\UseCases\Review\UpdateReviewUseCase;
use App\UseCases\Review\DeleteReviewUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class ReviewController extends Controller
{
    public function __construct(
        private GetAllReviewsUseCase $getAllReviewsUseCase,
        private GetReviewByIdUseCase $getReviewByIdUseCase,
        private GetReviewsByShopIdUseCase $getReviewsByShopIdUseCase,
        private CreateReviewUseCase $createReviewUseCase,
        private UpdateReviewUseCase $updateReviewUseCase,
        private DeleteReviewUseCase $deleteReviewUseCase
    ) {}

    /**
     * Display a listing of the reviews.
     */
    public function index(): JsonResponse
    {
        try {
            $reviews = $this->getAllReviewsUseCase->execute();
            
            return response()->json([
                'status' => 'success',
                'data' => $reviews
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified review.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $review = $this->getReviewByIdUseCase->execute($id);
            
            if (!$review) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'レビューが見つかりません'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $review
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reviews for a specific shop.
     */
    public function getByShop($shopId): JsonResponse
    {
        try {
            $reviews = $this->getReviewsByShopIdUseCase->execute($shopId);
            
            return response()->json([
                'status' => 'success',
                'data' => $reviews
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if (!Auth::guard('user')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'shop_id' => 'required|exists:shops,id',
                'rating' => 'required|numeric|between:1,5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $validated['user_id'] = Auth::guard('user')->id();

            $review = $this->createReviewUseCase->execute($validated);

            // レビュー作成後、ユーザー情報も含めて返す
            $review->load('user:id,name');

            return response()->json([
                'status' => 'success',
                'message' => 'レビューを投稿しました',
                'data' => $review
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 409);
        }
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if (!Auth::guard('user')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'rating' => 'required|numeric|between:1,5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $review = $this->updateReviewUseCase->execute($id, $validated);

            return response()->json([
                'status' => 'success',
                'message' => 'レビューを更新しました',
                'data' => $review
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            $statusCode = str_contains($e->getMessage(), '見つかりません') ? 404 : 400;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        if (!Auth::guard('user')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401);
        }

        try {
            $this->deleteReviewUseCase->execute($id);

            return response()->json([
                'status' => 'success',
                'message' => 'レビューを削除しました'
            ]);
        } catch (Exception $e) {
            $statusCode = str_contains($e->getMessage(), '見つかりません') ? 404 : 400;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
