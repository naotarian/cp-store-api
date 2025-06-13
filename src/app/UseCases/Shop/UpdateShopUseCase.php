<?php

namespace App\UseCases\Shop;

use App\Services\Shop\ShopService;

class UpdateShopUseCase
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function execute(string $id, array $data)
    {
        return $this->shopService->updateShop($id, $data);
    }
} 