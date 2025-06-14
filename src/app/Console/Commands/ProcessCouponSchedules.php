<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Coupon\CouponScheduleBatchService;
use Carbon\Carbon;

class ProcessCouponSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon:process-schedules {--date= : 処理対象日 (YYYY-MM-DD形式、省略時は翌日)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'スケジュールに基づいてクーポンを自動発行します';

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
        $this->info('クーポンスケジュール処理を開始します...');

        // 処理対象日を決定（デフォルトは翌日）
        $targetDate = $this->option('date') 
            ? Carbon::parse($this->option('date'))
            : Carbon::tomorrow();

        $this->info("処理対象日: {$targetDate->format('Y-m-d')}");

        try {
            $result = $this->batchService->processSchedulesForDate($targetDate);

            $this->info("処理完了:");
            $this->info("- 処理対象スケジュール数: {$result['total_schedules']}");
            $this->info("- 発行されたクーポン数: {$result['issued_coupons']}");
            $this->info("- スキップされたスケジュール数: {$result['skipped_schedules']}");

            if (!empty($result['errors'])) {
                $this->warn("エラーが発生したスケジュール:");
                foreach ($result['errors'] as $error) {
                    $this->error("- スケジュールID {$error['schedule_id']}: {$error['message']}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("バッチ処理中にエラーが発生しました: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
} 