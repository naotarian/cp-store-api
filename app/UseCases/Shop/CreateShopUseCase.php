<?php

namespace App\UseCases\Shop;

use App\Services\Shop\ShopService;

class CreateShopUseCase
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function execute(array $data)
    {
        return $this->shopService->createShop($data);
    }
} 