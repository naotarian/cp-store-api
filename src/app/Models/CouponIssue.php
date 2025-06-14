<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CouponIssue extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'coupon_id',
        'shop_id',
        'schedule_id',
        'issue_type',
        'target_date',
        'start_time_only',
        'end_time_only',
        'start_time',
        'end_time',
        'max_acquisitions',
        'current_acquisitions',
        'status',
        'is_active',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'issued_at' => 'datetime',
        'is_active' => 'boolean',
        'max_acquisitions' => 'integer',
        'current_acquisitions' => 'integer',
    ];

    protected $appends = [
        'is_available',
        'remaining_count',
        'time_remaining',
        'duration_minutes',
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
     * スケジュールとの関係
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CouponSchedule::class);
    }

    /**
     * 発行者との関係
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(ShopAdmin::class, 'issued_by');
    }

    /**
     * クーポン取得との関係
     */
    public function acquisitions(): HasMany
    {
        return $this->hasMany(CouponAcquisition::class);
    }

    /**
     * アクティブなクーポン取得のみ
     */
    public function activeAcquisitions(): HasMany
    {
        return $this->hasMany(CouponAcquisition::class)->where('status', 'active');
    }

    /**
     * 利用可能かどうか
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'active' &&
               $this->is_active &&
               now()->between($this->start_time, $this->end_time) &&
               ($this->max_acquisitions === null || $this->current_acquisitions < $this->max_acquisitions);
    }

    /**
     * 残り取得可能数
     */
    public function getRemainingCountAttribute(): ?int
    {
        if ($this->max_acquisitions === null) {
            return null; // 無制限
        }
        
        return max(0, $this->max_acquisitions - $this->current_acquisitions);
    }

    /**
     * 残り時間（分）
     */
    public function getTimeRemainingAttribute(): ?int
    {
        if (now()->isAfter($this->end_time)) {
            return 0;
        }

        return now()->diffInMinutes($this->end_time);
    }

    /**
     * 継続時間（分）
     */
    public function getDurationMinutesAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * アクティブなクーポン発行のスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    /**
     * 利用可能なクーポン発行のスコープ
     */
    public function scopeAvailable($query)
    {
        return $query->active()
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>', now());
    }

    /**
     * 店舗別のスコープ
     */
    public function scopeForShop($query, string $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * 発行タイプ別のスコープ
     */
    public function scopeByIssueType($query, string $type)
    {
        return $query->where('issue_type', $type);
    }

    /**
     * 取得数を増やす
     */
    public function incrementAcquisition(): bool
    {
        if ($this->max_acquisitions !== null && $this->current_acquisitions >= $this->max_acquisitions) {
            return false;
        }

        $this->increment('current_acquisitions');
        
        // 上限に達した場合はステータスを変更
        if ($this->max_acquisitions !== null && $this->current_acquisitions >= $this->max_acquisitions) {
            $this->update(['status' => 'full']);
        }

        return true;
    }

    /**
     * 期限切れをチェック
     */
    public function checkExpiration(): void
    {
        if (now()->isAfter($this->end_time) && $this->status === 'active') {
            $this->update(['status' => 'expired']);
        }
    }
}
