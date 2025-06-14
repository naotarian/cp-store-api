<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'image',
        'open_time',
        'close_time',
        'address',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
    ];

    protected $appends = [
        'average_rating',
        'review_count',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // お気に入りへのリレーション
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // お気に入りしているユーザーへの多対多リレーション
    public function favoriteUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'shop_id', 'user_id')
                    ->withTimestamps();
    }

    // 店舗管理者へのリレーション
    public function admins()
    {
        return $this->hasMany(ShopAdmin::class);
    }

    // 店舗管理者へのリレーション（別名）- Seederとの互換性のため
    public function shopAdmins()
    {
        return $this->hasMany(ShopAdmin::class);
    }

    // アクティブな管理者のみ
    public function activeAdmins()
    {
        return $this->hasMany(ShopAdmin::class)->where('is_active', true);
    }

    // ルート管理者を取得
    public function rootAdmin()
    {
        return $this->hasOne(ShopAdmin::class)->where('role', 'root');
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    // お気に入り数を取得
    public function getFavoriteCountAttribute()
    {
        return $this->favorites()->count();
    }
}
