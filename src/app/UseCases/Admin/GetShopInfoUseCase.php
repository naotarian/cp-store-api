<?php

namespace App\UseCases\Admin;

use App\Services\Admin\ShopService;
use App\Models\ShopAdmin;

class GetShopInfoUseCase
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function execute(ShopAdmin $shopAdmin)
    {
        return $this->shopService->getShopByAdminId($shopAdmin->id);
    }
} 