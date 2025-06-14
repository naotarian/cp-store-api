<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Coupon\CouponScheduleBatchService;
use Carbon\Carbon;

class TestCouponSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon:test-schedules {--date= : テスト対象日 (YYYY-MM-DD形式、省略時は今日)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'クーポンスケジュール処理をテスト実行します（実際の発行は行いません）';

    private $batchService;

    public function __construct(CouponScheduleBatchService $batchService)
    {
        parent::__construct();
        $this->batchService = $batchService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('クーポンスケジュール処理のテストを開始します...');

        // テスト対象日を決定
        $targetDate = $this->option('date') 
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $this->info("テスト対象日: {$targetDate->format('Y-m-d')} ({$targetDate->format('l')})");

        // 対象スケジュールを取得して表示
        $schedules = \App\Models\CouponSchedule::with(['coupon', 'shop'])
            ->where('is_active', true)
            ->where(function ($query) use ($targetDate) {
                $query->where('valid_from', '<=', $targetDate->format('Y-m-d'))
                      ->where(function ($q) use ($targetDate) {
                          $q->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', $targetDate->format('Y-m-d'));
                      });
            })
            ->get();

        $this->info("全スケジュール数: {$schedules->count()}");

        $applicableSchedules = $schedules->filter(function ($schedule) use ($targetDate) {
            return $schedule->shouldExecuteOnDate($targetDate);
        });

        $this->info("対象日に該当するスケジュール数: {$applicableSchedules->count()}");

        if ($applicableSchedules->isEmpty()) {
            $this->warn('該当するスケジュールがありません。');
            return Command::SUCCESS;
        }

        // 該当スケジュールの詳細を表示
        $this->table(
            ['ID', '店舗', 'クーポン名', 'スケジュール名', '曜日タイプ', '時間', '最終処理日'],
            $applicableSchedules->map(function ($schedule) {
                $startTime = is_string($schedule->start_time) 
                    ? $schedule->start_time 
                    : $schedule->start_time->format('H:i');
                $endTime = is_string($schedule->end_time) 
                    ? $schedule->end_time 
                    : $schedule->end_time->format('H:i');
                    
                return [
                    $schedule->id,
                    $schedule->shop->name ?? 'N/A',
                    $schedule->coupon->title ?? 'N/A',
                    $schedule->schedule_name,
                    $schedule->day_type_display,
                    "{$startTime} - {$endTime}",
                    $schedule->last_batch_processed_date?->format('Y-m-d') ?? '未処理'
                ];
            })->toArray()
        );

        // 実際に処理されるスケジュールをチェック
        $processableSchedules = $applicableSchedules->filter(function ($schedule) use ($targetDate) {
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
        });

        $this->info("実際に処理されるスケジュール数: {$processableSchedules->count()}");

        if ($processableSchedules->count() !== $applicableSchedules->count()) {
            $skippedSchedules = $applicableSchedules->diff($processableSchedules);
            $this->warn("スキップされるスケジュール:");
            foreach ($skippedSchedules as $schedule) {
                $reason = '';
                if (!$schedule->coupon || !$schedule->coupon->is_active) {
                    $reason = 'クーポンが無効';
                } elseif ($schedule->last_batch_processed_date && 
                         $schedule->last_batch_processed_date->format('Y-m-d') === $targetDate->format('Y-m-d')) {
                    $reason = '既に処理済み';
                }
                $this->line("  - {$schedule->schedule_name} ({$reason})");
            }
        }

        $this->info('テスト完了');
        return Command::SUCCESS;
    }
} 