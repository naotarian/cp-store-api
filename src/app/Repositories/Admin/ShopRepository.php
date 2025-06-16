<?php

namespace App\Repositories\Admin;

use App\Models\Shop;
use App\Models\ShopAdmin;

class ShopRepository implements ShopRepositoryInterface
{
    public function findByShopAdminId(string $shopAdminId)
    {
        $shopAdmin = ShopAdmin::find($shopAdminId);
        if (!$shopAdmin) {
            return null;
        }
        return $shopAdmin->shop;
    }

    public function updateByShopAdminId(string $shopAdminId, array $data)
    {
        $shopAdmin = ShopAdmin::find($shopAdminId);
        if (!$shopAdmin || !$shopAdmin->shop) {
            return null;
        }

        $shop = $shopAdmin->shop;
        $shop->update($data);
        return $shop->fresh();
    }

    public function findById(string $id)
    {
        return Shop::find($id);
    }

    public function create(array $data)
    {
        return Shop::create($data);
    }

    public function update(string $id, array $data)
    {
        $shop = Shop::find($id);
        if ($shop) {
            $shop->update($data);
            return $shop;
        }
        return null;
    }

    public function delete(string $id)
    {
        return Shop::destroy($id);
    }
} 