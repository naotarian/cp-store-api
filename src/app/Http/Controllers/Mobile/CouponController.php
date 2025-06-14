<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\UseCases\Mobile\Coupon\GetShopCouponsUseCase;
use App\UseCases\Mobile\Coupon\GetActiveIssuesUseCase;
use App\UseCases\Mobile\Coupon\AcquireCouponUseCase;
use App\UseCases\Mobile\Coupon\GetUserCouponsUseCase;
use App\UseCases\Mobile\Coupon\UseCouponUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * モバイルアプリ用クーポンコントローラー
 * 
 * 顧客がクーポンを閲覧・取得・利用するためのAPI
 */
class CouponController extends Controller
{
    public function __construct(
        private GetShopCouponsUseCase $getShopCouponsUseCase,
        private GetActiveIssuesUseCase $getActiveIssuesUseCase,
        private AcquireCouponUseCase $acquireCouponUseCase,
        private GetUserCouponsUseCase $getUserCouponsUseCase,
        private UseCouponUseCase $useCouponUseCase
    ) {}

    /**
     * 特定店舗のクーポン一覧取得
     * 
     * @param string $shopId
     * @return JsonResponse
     */
    public function getShopCoupons(string $shopId): JsonResponse
    {
        try {
            $result = $this->getShopCouponsUseCase->execute($shopId);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'coupons' => $result
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * 特定店舗の現在発行中のクーポン一覧取得
     * 
     * @param string $shopId
     * @return JsonResponse
     */
    public function getActiveIssues(string $shopId): JsonResponse
    {
        try {
            $result = $this->getActiveIssuesUseCase->execute($shopId);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'active_issues' => $result
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * クーポンを取得
     * 
     * @param Request $request
     * @param string $issueId
     * @return JsonResponse
     */
    public function acquireCoupon(Request $request, string $issueId): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $result = $this->acquireCouponUseCase->execute($issueId, $userId);
            
            return response()->json([
                'status' => 'success',
                'data' => $result,
                'message' => 'クーポンを取得しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * ユーザーの取得済みクーポン一覧取得
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserCoupons(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $result = $this->getUserCouponsUseCase->execute($userId);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'acquisitions' => $result
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * クーポンを使用
     * 
     * @param Request $request
     * @param string $acquisitionId
     * @return JsonResponse
     */
    public function useCoupon(Request $request, string $acquisitionId): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $notes = $request->input('notes', '');
            
            $this->useCouponUseCase->execute($acquisitionId, $userId, $notes);
            
            return response()->json([
                'status' => 'success',
                'message' => 'クーポンを使用しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
} 