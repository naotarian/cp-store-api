<?php

namespace App\Services\Shop;

use App\Repositories\Shop\ShopRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ShopService
{
    private $shopRepository;

    public function __construct(ShopRepositoryInterface $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    public function getAllShops(?float $latitude = null, ?float $longitude = null, ?float $radiusKm = null)
    {
        if ($latitude !== null && $longitude !== null && $radiusKm !== null) {
            return $this->shopRepository->findByLocation($latitude, $longitude, $radiusKm);
        }
        return $this->shopRepository->findAll($latitude, $longitude);
    }

    public function getShopById(string $id)
    {
        $shop = $this->shopRepository->findById($id);
        if (!$shop) {
            throw new \Exception('店舗が見つかりませんでした');
        }
        return $shop;
    }

    public function createShop(array $data)
    {
        DB::beginTransaction();
        try {
            $shop = $this->shopRepository->create($data);
            DB::commit();
            return $shop;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('店舗の作成に失敗しました: ' . $e->getMessage());
        }
    }

    public function updateShop(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $shop = $this->shopRepository->findById($id);
            if (!$shop) {
                throw new \Exception('店舗が見つかりませんでした');
            }

            $updatedShop = $this->shopRepository->update($id, $data);
            DB::commit();
            return $updatedShop;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('店舗の更新に失敗しました: ' . $e->getMessage());
        }
    }

    public function deleteShop(string $id)
    {
        DB::beginTransaction();
        try {
            $shop = $this->shopRepository->findById($id);
            if (!$shop) {
                throw new \Exception('店舗が見つかりませんでした');
            }

            $deleted = $this->shopRepository->delete($id);
            if (!$deleted) {
                throw new \Exception('店舗の削除に失敗しました');
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('店舗の削除に失敗しました: ' . $e->getMessage());
        }
    }
} 