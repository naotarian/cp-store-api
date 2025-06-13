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

    public function execute()
    {
        return $this->shopService->getAllShops();
    }
} 