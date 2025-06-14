<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCouponScheduleRequest;
use App\Http\Requests\Admin\UpdateCouponScheduleRequest;
use App\UseCases\Coupon\GetAllCouponsUseCase;
use App\UseCases\Coupon\GetActiveCouponIssuesUseCase;
use App\UseCases\Coupon\GetCouponSchedulesUseCase;
use App\UseCases\Coupon\CreateCouponScheduleUseCase;
use App\UseCases\Coupon\UpdateCouponScheduleUseCase;
use App\UseCases\Coupon\DeleteCouponScheduleUseCase;
use App\UseCases\Coupon\ToggleCouponScheduleStatusUseCase;
use App\UseCases\Coupon\GetTodaySchedulesUseCase;
use App\UseCases\Coupon\IssueCouponNowUseCase;
use App\UseCases\Coupon\StopCouponIssueUseCase;
use App\UseCases\Coupon\CreateCouponUseCase;
use App\UseCases\Coupon\UpdateCouponUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    private $getAllCouponsUseCase;
    private $getActiveCouponIssuesUseCase;
    private $getCouponSchedulesUseCase;
    private $createCouponScheduleUseCase;
    private $updateCouponScheduleUseCase;
    private $deleteCouponScheduleUseCase;
    private $toggleCouponScheduleStatusUseCase;
    private $getTodaySchedulesUseCase;
    private $issueCouponNowUseCase;
    private $stopCouponIssueUseCase;
    private $createCouponUseCase;
    private $updateCouponUseCase;

    public function __construct(
        GetAllCouponsUseCase $getAllCouponsUseCase,
        GetActiveCouponIssuesUseCase $getActiveCouponIssuesUseCase,
        GetCouponSchedulesUseCase $getCouponSchedulesUseCase,
        CreateCouponScheduleUseCase $createCouponScheduleUseCase,
        UpdateCouponScheduleUseCase $updateCouponScheduleUseCase,
        DeleteCouponScheduleUseCase $deleteCouponScheduleUseCase,
        ToggleCouponScheduleStatusUseCase $toggleCouponScheduleStatusUseCase,
        GetTodaySchedulesUseCase $getTodaySchedulesUseCase,
        IssueCouponNowUseCase $issueCouponNowUseCase,
        StopCouponIssueUseCase $stopCouponIssueUseCase,
        CreateCouponUseCase $createCouponUseCase,
        UpdateCouponUseCase $updateCouponUseCase
    ) {
        $this->getAllCouponsUseCase = $getAllCouponsUseCase;
        $this->getActiveCouponIssuesUseCase = $getActiveCouponIssuesUseCase;
        $this->getCouponSchedulesUseCase = $getCouponSchedulesUseCase;
        $this->createCouponScheduleUseCase = $createCouponScheduleUseCase;
        $this->updateCouponScheduleUseCase = $updateCouponScheduleUseCase;
        $this->deleteCouponScheduleUseCase = $deleteCouponScheduleUseCase;
        $this->toggleCouponScheduleStatusUseCase = $toggleCouponScheduleStatusUseCase;
        $this->getTodaySchedulesUseCase = $getTodaySchedulesUseCase;
        $this->issueCouponNowUseCase = $issueCouponNowUseCase;
        $this->stopCouponIssueUseCase = $stopCouponIssueUseCase;
        $this->createCouponUseCase = $createCouponUseCase;
        $this->updateCouponUseCase = $updateCouponUseCase;
    }

    /**
     * 新しいクーポンを作成
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'conditions' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'image_url' => 'nullable|url|max:500',
        ]);

        try {
            $admin = Auth::user();
            $coupon = $this->createCouponUseCase->execute($admin, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'クーポンを作成しました',
                'data' => [
                    'coupon' => $this->formatCouponResponse($coupon)
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * クーポンを更新
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'conditions' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'image_url' => 'nullable|url|max:500',
        ]);

        try {
            $admin = Auth::user();
            $coupon = $this->updateCouponUseCase->execute($admin, $id, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'クーポンを更新しました',
                'data' => [
                    'coupon' => $this->formatCouponResponse($coupon)
                ]
            ]);
        } catch (\Exception $e) {
            // クーポンが見つからない場合は404を返す
            if (str_contains($e->getMessage(), 'クーポンが見つかりません') || 
                str_contains($e->getMessage(), '権限がありません')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 404);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 店舗の全クーポン一覧を取得
     */
    public function index(): JsonResponse
    {
        $admin = Auth::user(); // sanctumミドルウェアで既に認証済み
        $coupons = $this->getAllCouponsUseCase->execute($admin);

        return response()->json([
            'status' => 'success',
            'data' => [
                'coupons' => $coupons->map(function ($coupon) {
                    $couponData = $this->formatCouponResponse($coupon);
                    // 関連データを追加
                    $couponData['active_issues_count'] = $coupon->activeIssues()->count();
                    $couponData['schedules_count'] = $coupon->activeSchedules()->count();
                    $couponData['total_issues_count'] = $coupon->issues()->count();
                    return $couponData;
                })
            ]
        ]);
    }

    /**
     * 現在発行中のクーポン一覧を取得
     */
    public function activeIssues(): JsonResponse
    {
        $admin = Auth::user(); // sanctumミドルウェアで既に認証済み
        $activeIssues = $this->getActiveCouponIssuesUseCase->execute($admin);

        return response()->json([
            'status' => 'success',
            'data' => [
                'active_issues' => $activeIssues->map(function ($issue) {
                    return [
                        'id' => $issue->id,
                        'coupon_id' => $issue->coupon_id,
                        'issue_type' => $issue->issue_type,
                        'start_datetime' => $issue->start_datetime->format('Y-m-d H:i:s'),
                        'end_datetime' => $issue->end_datetime->format('Y-m-d H:i:s'),
                        'duration_minutes' => $issue->duration_minutes,
                        'max_acquisitions' => $issue->max_acquisitions,
                        'current_acquisitions' => $issue->current_acquisitions,
                        'remaining_count' => $issue->remaining_count,
                        'time_remaining' => $issue->time_remaining,
                        'is_available' => $issue->is_available,
                        'status' => $issue->status,
                        'issued_at' => $issue->issued_at->format('Y-m-d H:i:s'),
                        // クーポン情報
                        'coupon' => [
                            'id' => $issue->coupon->id,
                            'title' => $issue->coupon->title,
                            'description' => $issue->coupon->description,
                            'conditions' => $issue->coupon->conditions,
                            'notes' => $issue->coupon->notes,
                        ],
                        // 発行者情報
                        'issuer' => $issue->issuer ? [
                            'id' => $issue->issuer->id,
                            'name' => $issue->issuer->name,
                        ] : null,
                    ];
                })
            ]
        ]);
    }

    /**
     * 時刻文字列をHH:MM形式にフォーマット
     */
    private function formatTimeString($time): string
    {
        if (is_string($time)) {
            // HH:MM:SS形式の場合はHH:MM形式に変換
            return strlen($time) > 5 ? substr($time, 0, 5) : $time;
        }
        return $time->format('H:i');
    }

    /**
     * クーポンレスポンス形成
     */
    private function formatCouponResponse($coupon): array
    {
        return [
            'id' => $coupon->id,
            'title' => $coupon->title,
            'description' => $coupon->description,
            'conditions' => $coupon->conditions,
            'notes' => $coupon->notes,
            'image_url' => $coupon->image_url,
            'is_active' => $coupon->is_active,
            'created_at' => $coupon->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $coupon->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * スケジュールレスポンス形成
     */
    private function formatScheduleResponse($schedule): array
    {
        return [
            'id' => $schedule->id,
            'coupon_id' => $schedule->coupon_id,
            'schedule_name' => $schedule->schedule_name,
            'day_type' => $schedule->day_type,
            'day_type_display' => $schedule->day_type_display,
            'custom_days' => $schedule->custom_days,
            'start_time' => $this->formatTimeString($schedule->start_time),
            'end_time' => $this->formatTimeString($schedule->end_time),
            'time_range_display' => $schedule->time_range_display,
            'duration_minutes' => $schedule->duration_minutes,
            'max_acquisitions' => $schedule->max_acquisitions,
            'valid_from' => $schedule->valid_from->format('Y-m-d'),
            'valid_until' => $schedule->valid_until ? $schedule->valid_until->format('Y-m-d') : null,
            'is_active' => $schedule->is_active,
            'created_at' => $schedule->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * スケジュール設定されたクーポン一覧を取得
     */
    public function schedules(): JsonResponse
    {
        $admin = Auth::user(); // sanctumミドルウェアで既に認証済み
        $schedules = $this->getCouponSchedulesUseCase->execute($admin);

        return response()->json([
            'status' => 'success',
            'data' => [
                'schedules' => $schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'coupon_id' => $schedule->coupon_id,
                        'schedule_name' => $schedule->schedule_name,
                        'day_type' => $schedule->day_type,
                        'day_type_display' => $schedule->day_type_display,
                        'custom_days' => $schedule->custom_days,
                        'start_time' => $this->formatTimeString($schedule->start_time),
                        'end_time' => $this->formatTimeString($schedule->end_time),
                        'time_range_display' => $schedule->time_range_display,
                        'duration_minutes' => $schedule->duration_minutes,
                        'max_acquisitions' => $schedule->max_acquisitions,
                        'valid_from' => $schedule->valid_from->format('Y-m-d'),
                        'valid_until' => $schedule->valid_until ? $schedule->valid_until->format('Y-m-d') : null,
                        'is_active' => $schedule->is_active,
                        'last_batch_processed_date' => $schedule->last_batch_processed_date ? $schedule->last_batch_processed_date->format('Y-m-d') : null,
                        // クーポン情報
                        'coupon' => [
                            'id' => $schedule->coupon->id,
                            'title' => $schedule->coupon->title,
                            'description' => $schedule->coupon->description,
                            'conditions' => $schedule->coupon->conditions,
                            'notes' => $schedule->coupon->notes,
                        ],
                    ];
                })
            ]
        ]);
    }

    /**
     * クーポンを即座に発行（スポット発行）
     */
    public function issueNow(Request $request, string $couponId): JsonResponse
    {
        $request->validate([
            'duration_minutes' => 'required|integer|min:30|max:1440', // 30分〜24時間
            'max_acquisitions' => 'nullable|integer|min:1|max:1000',
        ]);

        try {
            $admin = Auth::user(); // sanctumミドルウェアで既に認証済み
            $issue = $this->issueCouponNowUseCase->execute($admin, $couponId, [
                'duration_minutes' => $request->duration_minutes,
                'max_acquisitions' => $request->max_acquisitions,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'クーポンを発行しました',
                'data' => [
                    'issue_id' => $issue->id,
                    'end_time' => $issue->end_datetime->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * クーポン発行を停止
     */
    public function stopIssue(string $issueId): JsonResponse
    {
        try {
            $admin = Auth::user(); // sanctumミドルウェアで既に認証済み
            $this->stopCouponIssueUseCase->execute($admin, $issueId);

            return response()->json([
                'status' => 'success',
                'message' => 'クーポン発行を停止しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * スケジュールを作成
     */
    public function createSchedule(CreateCouponScheduleRequest $request): JsonResponse
    {
        try {
            $admin = Auth::user();
            $schedule = $this->createCouponScheduleUseCase->execute($admin, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'スケジュールを作成しました',
                'data' => [
                    'schedule' => $this->formatScheduleResponse($schedule)
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * スケジュールを更新
     */
    public function updateSchedule(UpdateCouponScheduleRequest $request, string $id): JsonResponse
    {
        try {
            $admin = Auth::user();
            $schedule = $this->updateCouponScheduleUseCase->execute($admin, $id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'スケジュールを更新しました',
                'data' => [
                    'schedule' => [
                        'id' => $schedule->id,
                        'schedule_name' => $schedule->schedule_name,
                        'day_type' => $schedule->day_type,
                        'day_type_display' => $schedule->day_type_display,
                        'start_time' => $this->formatTimeString($schedule->start_time),
                        'end_time' => $this->formatTimeString($schedule->end_time),
                        'time_range_display' => $schedule->time_range_display,
                        'duration_minutes' => $schedule->duration_minutes,
                        'max_acquisitions' => $schedule->max_acquisitions,
                        'is_active' => $schedule->is_active,
                        'updated_at' => $schedule->updated_at->format('Y-m-d H:i:s'),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * スケジュールを削除
     */
    public function deleteSchedule(string $id): JsonResponse
    {
        try {
            $admin = Auth::user();
            $this->deleteCouponScheduleUseCase->execute($admin, $id);

            return response()->json([
                'status' => 'success',
                'message' => 'スケジュールを削除しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * スケジュールの有効/無効を切り替え
     */
    public function toggleScheduleStatus(string $id): JsonResponse
    {
        try {
            Log::info('toggleScheduleStatus', ['id' => $id]);
            $admin = Auth::user();
            $schedule = $this->toggleCouponScheduleStatusUseCase->execute($admin, $id);

            return response()->json([
                'status' => 'success',
                'message' => $schedule->is_active ? 'スケジュールを有効にしました' : 'スケジュールを無効にしました',
                'data' => [
                    'schedule' => [
                        'id' => $schedule->id,
                        'is_active' => $schedule->is_active,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 今日のスケジュール一覧を取得
     */
    public function todaySchedules(): JsonResponse
    {
        try {
            $admin = Auth::user();
            $schedules = $this->getTodaySchedulesUseCase->execute($admin);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'today_schedules' => $schedules->map(function ($schedule) {
                        return [
                            'id' => $schedule->id,
                            'coupon_id' => $schedule->coupon_id,
                            'schedule_name' => $schedule->schedule_name,
                            'day_type_display' => $schedule->day_type_display,
                            'time_range_display' => $schedule->time_range_display,
                            'start_time' => $this->formatTimeString($schedule->start_time),
                            'end_time' => $this->formatTimeString($schedule->end_time),
                            'duration_minutes' => $schedule->duration_minutes,
                            'max_acquisitions' => $schedule->max_acquisitions,
                            'is_active' => $schedule->is_active,
                            // クーポン情報
                            'coupon' => [
                                'id' => $schedule->coupon->id,
                                'title' => $schedule->coupon->title,
                            ],
                            // 作成者情報
                            'creator' => $schedule->creator ? [
                                'id' => $schedule->creator->id,
                                'name' => $schedule->creator->name,
                            ] : null,
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
} 