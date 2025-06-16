<?php

namespace App\Repositories\Admin;

interface ShopRepositoryInterface
{
    public function findByShopAdminId(string $shopAdminId);
    public function updateByShopAdminId(string $shopAdminId, array $data);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
    public function findById(string $id);
} 