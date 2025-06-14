<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'shop_id',
        'title',
        'description',
        'conditions',
        'notes',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 店舗との関係
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * クーポン発行との関係
     */
    public function issues(): HasMany
    {
        return $this->hasMany(CouponIssue::class);
    }

    /**
     * アクティブなクーポン発行のみ（利用可能なもののみ）
     */
    public function activeIssues(): HasMany
    {
        return $this->hasMany(CouponIssue::class)->available();
    }

    /**
     * クーポンスケジュールとの関係
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(CouponSchedule::class);
    }

    /**
     * アクティブなスケジュールのみ
     */
    public function activeSchedules(): HasMany
    {
        return $this->hasMany(CouponSchedule::class)->where('is_active', true);
    }



    /**
     * アクティブなクーポンのスコープ
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


}
