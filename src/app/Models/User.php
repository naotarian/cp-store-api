<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * @method string generateApiToken()
 * @method bool update(array $attributes = [])
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUlids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
        ];
    }

    // お気に入りへのリレーション
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // お気に入り店舗への多対多リレーション
    public function favoriteShops()
    {
        return $this->belongsToMany(Shop::class, 'favorites', 'user_id', 'shop_id')
                    ->withTimestamps();
    }

    // 特定の店舗がお気に入りかどうかチェック
    public function isFavoriteShop($shopId)
    {
        return $this->favorites()->where('shop_id', $shopId)->exists();
    }

    // レビューへのリレーション
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // カスタムAPIトークン生成
    public function generateApiToken()
    {
        $token = Str::random(80);
        $this->update(['api_token' => hash('sha256', $token)]);
        return $token;
    }
}
