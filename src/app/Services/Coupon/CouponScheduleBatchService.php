<?php

namespace App\Services\Coupon;

use App\Repositories\Coupon\CouponRepository;
use App\Models\CouponSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CouponScheduleBatchService
{
    private $couponRepository;

    public function __construct(CouponRepository $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    /**
     * 指定日のスケジュールを処理してクーポンを発行
     */
    public function processSchedulesForDate(Carbon $targetDate): array
    {
        $result = [
            'total_schedules' => 0,
            'issued_coupons' => 0,
            'skipped_schedules' => 0,
            'errors' => []
        ];

        Log::info("クーポンスケジュール処理開始", [
            'target_date' => $targetDate->format('Y-m-d')
        ]);

        // 対象日のスケジュールを取得
        $schedules = $this->getSchedulesForDate($targetDate);
        $result['total_schedules'] = $schedules->count();

        foreach ($schedules as $schedule) {
            try {
                if ($this->shouldProcessSchedule($schedule, $targetDate)) {
                    $this->processSchedule($schedule, $targetDate);
                    $result['issued_coupons']++;
                    
                    Log::info("スケジュール処理完了", [
                        'schedule_id' => $schedule->id,
                        'coupon_id' => $schedule->coupon_id,
                        'target_date' => $targetDate->format('Y-m-d')
                    ]);
                } else {
                    $result['skipped_schedules']++;
                    
                    Log::info("スケジュールをスキップ", [
                        'schedule_id' => $schedule->id,
                        'reason' => 'Already processed or conditions not met'
                    ]);
                }
            } catch (\Exception $e) {
                $result['errors'][] = [
                    'schedule_id' => $schedule->id,
                    'message' => $e->getMessage()
                ];
                
                Log::error("スケジュール処理エラー", [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info("クーポンスケジュール処理完了", $result);

        return $result;
    }

    /**
     * 指定日に該当するスケジュールを取得
     */
    private function getSchedulesForDate(Carbon $targetDate)
    {
        $dayOfWeek = $targetDate->dayOfWeek; // 0=日曜日, 1=月曜日, ..., 6=土曜日

        // まず基本的な条件でスケジュールを取得
        $schedules = CouponSchedule::with(['coupon', 'shop'])
            ->where('is_active', true)
            ->where(function ($query) use ($targetDate) {
                // valid_fromとvalid_untilの範囲チェック
                $query->where('valid_from', '<=', $targetDate->format('Y-m-d'))
                      ->where(function ($q) use ($targetDate) {
                          $q->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', $targetDate->format('Y-m-d'));
                      });
            })
            ->get();

        // PHPレベルで曜日条件をフィルタリング
        return $schedules->filter(function ($schedule) use ($dayOfWeek) {
            switch ($schedule->day_type) {
                case 'daily':
                    return true;
                case 'weekdays':
                    return in_array($dayOfWeek, [1, 2, 3, 4, 5]);
                case 'weekends':
                    return in_array($dayOfWeek, [0, 6]);
                case 'custom':
                    $customDays = is_string($schedule->custom_days) 
                        ? json_decode($schedule->custom_days, true) 
                        : $schedule->custom_days;
                    return is_array($customDays) && in_array($dayOfWeek, $customDays);
                default:
                    return false;
            }
        });
    }

    /**
     * スケジュールを処理すべきかチェック
     */
    private function shouldProcessSchedule(CouponSchedule $schedule, Carbon $targetDate): bool
    {
        // クーポンがアクティブかチェック
        if (!$schedule->coupon || !$schedule->coupon->is_active) {
            return false;
        }

        // 既に同じ日に処理済みかチェック
        if ($schedule->last_batch_processed_date && 
            $schedule->last_batch_processed_date->format('Y-m-d') === $targetDate->format('Y-m-d')) {
            return false;
        }

        return true;
    }

    /**
     * スケジュールを処理してクーポンを発行
     */
    private function processSchedule(CouponSchedule $schedule, Carbon $targetDate): void
    {
        DB::transaction(function () use ($schedule, $targetDate) {
            // 時刻文字列を取得（HH:MM形式）
            $startTimeString = is_string($schedule->start_time) 
                ? $schedule->start_time 
                : $schedule->start_time->format('H:i');
            $endTimeString = is_string($schedule->end_time) 
                ? $schedule->end_time 
                : $schedule->end_time->format('H:i');

            // 発行開始時刻と終了時刻を計算
            $startDateTime = $targetDate->copy()
                ->setTimeFromTimeString($startTimeString);
            $endDateTime = $targetDate->copy()
                ->setTimeFromTimeString($endTimeString);

            // 終了時刻が開始時刻より前の場合は翌日とみなす
            if ($endDateTime->lt($startDateTime)) {
                $endDateTime->addDay();
            }

            // クーポン発行データを作成
            $issueData = [
                'shop_id' => $schedule->shop_id,
                'issue_type' => 'batch_generated',
                'start_datetime' => $startDateTime,
                'end_datetime' => $endDateTime,
                'max_acquisitions' => $schedule->max_acquisitions,
                'current_acquisitions' => 0,
                'status' => 'active',
                'is_active' => true,
                'issued_by' => null, // バッチ処理なのでnull
                'issued_at' => Carbon::now(),
            ];

            // クーポンを発行
            $this->couponRepository->issueCoupon($schedule->coupon_id, $issueData);

            // スケジュールの最終処理日を更新
            $schedule->update([
                'last_batch_processed_date' => $targetDate
            ]);
        });
    }
} 