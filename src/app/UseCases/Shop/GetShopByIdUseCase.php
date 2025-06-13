<?php

namespace App\UseCases\Shop;

use App\Services\Shop\ShopService;

class GetShopByIdUseCase
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function execute(string $id)
    {
        return $this->shopService->getShopById($id);
    }
} 