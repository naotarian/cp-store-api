<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CouponAcquisition extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'coupon_issue_id',
        'user_id',
        'acquired_at',
        'used_at',
        'expired_at',
        'status',
        'processed_by',
        'usage_notes',
        'is_notification_read',
        'notification_read_at',
        'is_banner_shown',
        'banner_shown_at',
    ];

    protected $casts = [
        'acquired_at' => 'datetime',
        'used_at' => 'datetime',
        'expired_at' => 'datetime',
        'notification_read_at' => 'datetime',
        'is_notification_read' => 'boolean',
        'banner_shown_at' => 'datetime',
        'is_banner_shown' => 'boolean',
    ];

    protected $appends = [
        'is_expired',
        'is_usable',
        'time_until_expiry',
    ];

    /**
     * クーポン発行との関係
     */
    public function couponIssue(): BelongsTo
    {
        return $this->belongsTo(CouponIssue::class);
    }

    /**
     * ユーザーとの関係
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 処理した店員との関係
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(ShopAdmin::class, 'processed_by');
    }

    /**
     * クーポン情報（through coupon_issue）
     */
    public function coupon(): BelongsTo
    {
        return $this->couponIssue()->getRelated()->coupon();
    }

    /**
     * 期限切れかどうか
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expired_at) {
            return false;
        }
        return now()->isAfter($this->expired_at);
    }

    /**
     * 使用可能かどうか
     */
    public function getIsUsableAttribute(): bool
    {
        return $this->status === 'active' && !$this->is_expired;
    }

    /**
     * 有効期限までの時間（分）
     */
    public function getTimeUntilExpiryAttribute(): ?int
    {
        if (!$this->expired_at || $this->is_expired) {
            return 0;
        }

        return now()->diffInMinutes($this->expired_at);
    }

    /**
     * アクティブなクーポン取得のスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * 使用済みのクーポン取得のスコープ
     */
    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    /**
     * 期限切れのクーポン取得のスコープ
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * 使用可能なクーポン取得のスコープ
     */
    public function scopeUsable($query)
    {
        return $query->active()->where('expired_at', '>', now());
    }

    /**
     * ユーザー別のスコープ
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 期限チェック
     */
    public function checkExpiration(): void
    {
        if ($this->is_expired && $this->status === 'active') {
            $this->update(['status' => 'expired']);
        }
    }

    /**
     * クーポンを使用済みにする
     */
    public function markAsUsed(?string $processedBy = null, ?string $notes = null): bool
    {
        if (!$this->is_usable) {
            return false;
        }

        $this->update([
            'status' => 'used',
            'used_at' => now(),
            'processed_by' => $processedBy,
            'usage_notes' => $notes,
        ]);

        return true;
    }

    /**
     * QRコード用の識別子を生成
     */
    public function generateQrData(): array
    {
        return [
            'acquisition_id' => $this->id,
            'user_id' => $this->user_id,
            'coupon_issue_id' => $this->coupon_issue_id,
            'expires_at' => $this->expired_at->toISOString(),
            'signature' => hash_hmac('sha256', $this->id . $this->user_id, config('app.key')),
        ];
    }

    /**
     * QRデータの検証
     */
    public static function validateQrData(array $data): bool
    {
        if (!isset($data['acquisition_id'], $data['user_id'], $data['signature'])) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $data['acquisition_id'] . $data['user_id'], config('app.key'));
        
        return hash_equals($expectedSignature, $data['signature']);
    }
}
