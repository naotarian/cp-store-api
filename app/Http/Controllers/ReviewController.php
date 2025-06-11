<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the reviews.
     */
    public function index(): JsonResponse
    {
        $reviews = Review::with('shop')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    }

    /**
     * Get reviews for a specific shop.
     */
    public function getByShop($shopId): JsonResponse
    {
        $reviews = Review::with('user:id,name')
                        ->where('shop_id', $shopId)
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => '認証が必要です'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'rating' => 'required|numeric|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // 同じユーザーが同じ店舗に複数レビューを投稿することを防ぐ
        $existingReview = Review::where('shop_id', $validated['shop_id'])
                               ->where('user_id', Auth::id())
                               ->first();

        if ($existingReview) {
            return response()->json([
                'status' => 'error',
                'message' => 'この店舗にはすでにレビューを投稿されています'
            ], 409)->header('Access-Control-Allow-Origin', '*');
        }

        $review = Review::create([
            'shop_id' => $validated['shop_id'],
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        // レビュー作成後、ユーザー情報も含めて返す
        $review->load('user:id,name');

        return response()->json([
            'status' => 'success',
            'message' => 'レビューを投稿しました',
            'data' => $review
        ], 201)->header('Access-Control-Allow-Origin', '*');
    }
}
