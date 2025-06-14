<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ShopAdmin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasUlids;

    /**e
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'phone',
        'notes',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * 店舗との関係
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * 発行したクーポンとの関係
     */
    public function issuedCoupons()
    {
        return $this->hasMany(CouponIssue::class, 'issued_by');
    }

    /**
     * 処理したクーポン取得との関係
     */
    public function processedCouponAcquisitions()
    {
        return $this->hasMany(CouponAcquisition::class, 'processed_by');
    }

    /**
     * ルート権限を持つかチェック
     */
    public function isRoot(): bool
    {
        return $this->role === 'root';
    }

    /**
     * マネージャー権限以上を持つかチェック
     */
    public function isManagerOrAbove(): bool
    {
        return in_array($this->role, ['root', 'manager']);
    }

    /**
     * アクティブなアカウントかチェック
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * 最終ログイン時刻を更新
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * 店舗のスコープ
     */
    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * アクティブなアカウントのスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * ロール別のスコープ
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
