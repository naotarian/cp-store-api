<?php

namespace App\UseCases\Shop;

use App\Services\Shop\ShopService;

class GetAllShopsUseCase
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function execute(?float $latitude = null, ?float $longitude = null, ?float $radiusKm = null)
    {
        \Log::info('uuu');
        return $this->shopService->getAllShops($latitude, $longitude, $radiusKm);
    }
} 