<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponSchedule extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'coupon_id',
        'shop_id',
        'schedule_name',
        'day_type',
        'custom_days',
        'start_time',
        'end_time',
        'max_acquisitions',
        'valid_from',
        'valid_until',
        'is_active',
        'last_batch_processed_date',
        'created_by',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'last_batch_processed_date' => 'date',
        'is_active' => 'boolean',
        'custom_days' => 'array',
        'max_acquisitions' => 'integer',
    ];

    /**
     * クーポンとの関係
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * 店舗との関係
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * アクティブなスケジュールのスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 店舗別のスコープ
     */
    public function scopeForShop($query, string $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * 作成者との関係
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(ShopAdmin::class, 'created_by');
    }

    /**
     * 今日実行されるスケジュールのスコープ
     */
    public function scopeTodaySchedules($query)
    {
        $today = now();
        $dayOfWeek = $today->dayOfWeek; // 0=日曜, 1=月曜, ..., 6=土曜

        return $query->where('is_active', true)
            ->where(function ($q) use ($dayOfWeek, $today) {
                $q->where('day_type', 'daily')
                  ->orWhere(function ($q2) use ($dayOfWeek) {
                      $q2->where('day_type', 'weekdays')
                         ->whereIn($dayOfWeek, [1, 2, 3, 4, 5]); // 月-金
                  })
                  ->orWhere(function ($q2) use ($dayOfWeek) {
                      $q2->where('day_type', 'weekends')
                         ->whereIn($dayOfWeek, [0, 6]); // 日,土
                  })
                  ->orWhere(function ($q2) use ($dayOfWeek) {
                      $q2->where('day_type', 'custom')
                         ->whereJsonContains('custom_days', $dayOfWeek);
                  });
            })
            ->where('valid_from', '<=', $today->toDateString())
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', $today->toDateString());
            });
    }

    /**
     * バッチ処理対象のスケジュールを取得
     */
    public function scopeForBatchProcessing($query, Carbon $targetDate)
    {
        $dayOfWeek = $targetDate->dayOfWeek;

        return $query->where('is_active', true)
            ->where(function ($q) use ($dayOfWeek) {
                $q->where('day_type', 'daily')
                  ->orWhere(function ($q2) use ($dayOfWeek) {
                      $q2->where('day_type', 'weekdays')
                         ->whereIn($dayOfWeek, [1, 2, 3, 4, 5]);
                  })
                  ->orWhere(function ($q2) use ($dayOfWeek) {
                      $q2->where('day_type', 'weekends')
                         ->whereIn($dayOfWeek, [0, 6]);
                  })
                  ->orWhere(function ($q2) use ($dayOfWeek) {
                      $q2->where('day_type', 'custom')
                         ->whereJsonContains('custom_days', $dayOfWeek);
                  });
            })
            ->where('valid_from', '<=', $targetDate->toDateString())
            ->where(function ($q) use ($targetDate) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', $targetDate->toDateString());
            })
            ->where(function ($q) use ($targetDate) {
                $q->whereNull('last_batch_processed_date')
                  ->orWhere('last_batch_processed_date', '<', $targetDate->toDateString());
            });
    }

    /**
     * 指定日に実行すべきかチェック
     */
    public function shouldExecuteOnDate(Carbon $date): bool
    {
        $dayOfWeek = $date->dayOfWeek;

        // 有効期間チェック
        if ($date->lt($this->valid_from) || 
            ($this->valid_until && $date->gt($this->valid_until))) {
            return false;
        }

        return match ($this->day_type) {
            'daily' => true,
            'weekdays' => in_array($dayOfWeek, [1, 2, 3, 4, 5]),
            'weekends' => in_array($dayOfWeek, [0, 6]),
            'custom' => in_array($dayOfWeek, $this->custom_days ?? []),
            default => false,
        };
    }

    /**
     * 曜日タイプの表示名を取得
     */
    public function getDayTypeDisplayAttribute(): string
    {
        return match ($this->day_type) {
            'daily' => '毎日',
            'weekdays' => '平日のみ',
            'weekends' => '土日のみ',
            'custom' => $this->getCustomDaysDisplay(),
            default => '不明',
        };
    }

    /**
     * カスタム曜日の表示名を取得
     */
    private function getCustomDaysDisplay(): string
    {
        if (empty($this->custom_days)) {
            return 'カスタム';
        }

        $dayNames = ['日', '月', '火', '水', '木', '金', '土'];
        $selectedDays = array_map(fn($day) => $dayNames[$day], $this->custom_days);
        
        return implode('・', $selectedDays);
    }

    /**
     * 時間範囲の表示名を取得
     */
    public function getTimeRangeDisplayAttribute(): string
    {
        $startTime = is_string($this->start_time) 
            ? $this->start_time 
            : $this->start_time->format('H:i');
        $endTime = is_string($this->end_time) 
            ? $this->end_time 
            : $this->end_time->format('H:i');
            
        // HH:MM:SS形式の場合はHH:MM形式に変換
        if (strlen($startTime) > 5) {
            $startTime = substr($startTime, 0, 5);
        }
        if (strlen($endTime) > 5) {
            $endTime = substr($endTime, 0, 5);
        }
            
        return $startTime . ' - ' . $endTime;
    }

    /**
     * 継続時間（分）を計算
     */
    public function getDurationMinutesAttribute(): int
    {
        $startTimeString = is_string($this->start_time) 
            ? $this->start_time 
            : $this->start_time->format('H:i');
        $endTimeString = is_string($this->end_time) 
            ? $this->end_time 
            : $this->end_time->format('H:i');
            
        // 時刻文字列の形式を判定してパース
        $startFormat = strlen($startTimeString) > 5 ? 'H:i:s' : 'H:i';
        $endFormat = strlen($endTimeString) > 5 ? 'H:i:s' : 'H:i';
        
        $start = Carbon::createFromFormat($startFormat, $startTimeString);
        $end = Carbon::createFromFormat($endFormat, $endTimeString);
        
        return $start->diffInMinutes($end);
    }
}
