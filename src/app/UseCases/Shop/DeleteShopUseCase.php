<?php

namespace App\UseCases\Shop;

use App\Services\Shop\ShopService;

class DeleteShopUseCase
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function execute(string $id)
    {
        return $this->shopService->deleteShop($id);
    }
} 