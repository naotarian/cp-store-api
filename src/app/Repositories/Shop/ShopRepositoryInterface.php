<?php

namespace App\Repositories\Shop;

interface ShopRepositoryInterface
{
    public function findAll();
    public function findById(string $id);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
} 