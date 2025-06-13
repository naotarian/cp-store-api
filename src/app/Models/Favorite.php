<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'shop_id',
    ];

    // ユーザーへのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 店舗へのリレーション
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
