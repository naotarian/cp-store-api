<?php

namespace App\UseCases\Admin;

use App\Services\Admin\ShopService;
use App\Models\ShopAdmin;

class UpdateShopInfoUseCase
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function execute(ShopAdmin $shopAdmin, array $data)
    {
        return $this->shopService->updateShopByAdminId($shopAdmin->id, $data);
    }
} 