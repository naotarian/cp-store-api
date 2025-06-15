<?php

namespace App\Repositories\Shop;

interface ShopRepositoryInterface
{
    public function findAll(?float $latitude = null, ?float $longitude = null);
    public function findById(string $id);
    public function findByLocation(float $latitude, float $longitude, float $radiusKm);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
} 