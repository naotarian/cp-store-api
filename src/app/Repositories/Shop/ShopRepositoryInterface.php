<?php

namespace App\Repositories\Shop;

interface ShopRepositoryInterface
{
    public function findAll(?float $latitude = null, ?float $longitude = null);
    public function findById(string $id);
    public function findByLocation(float $latitude, float $longitude, float $radiusKm);
} 