<?php

namespace App\Repositories\Shop;

use App\Common\RepositoryInterface;
use App\Models\Shop;

class ShopRepository implements ShopRepositoryInterface
{
    public function findAll(?float $latitude = null, ?float $longitude = null)
    {
        if ($latitude !== null && $longitude !== null) {
            // 位置情報が提供されている場合は距離を計算して含める
            return Shop::selectRaw("
                *,
                ROUND(
                    6371000 * acos(
                        cos(radians(?)) * 
                        cos(radians(latitude)) * 
                        cos(radians(longitude) - radians(?)) + 
                        sin(radians(?)) * 
                        sin(radians(latitude))
                    )
                ) AS distance_meters
            ", [$latitude, $longitude, $latitude])
            ->orderBy('distance_meters')
            ->get();
        }
        
        return Shop::all();
    }

    public function findById(string $id)
    {
        return Shop::find($id);
    }

    public function findByLocation(float $latitude, float $longitude, float $radiusKm)
    {
        // Haversine公式を使用して距離を計算（メートル単位）
        return Shop::selectRaw("
            *,
            ROUND(
                6371000 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )
            ) AS distance_meters,
            (
                6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )
            ) AS distance_km
        ", [$latitude, $longitude, $latitude, $latitude, $longitude, $latitude])
        ->having('distance_km', '<=', $radiusKm)
        ->orderBy('distance_km')
        ->get();
    }
} 