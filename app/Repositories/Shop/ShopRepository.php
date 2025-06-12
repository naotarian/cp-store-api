<?php

namespace App\Repositories\Shop;

use App\Common\RepositoryInterface;
use App\Models\Shop;

class ShopRepository implements ShopRepositoryInterface
{
    public function findAll()
    {
        return Shop::all();
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