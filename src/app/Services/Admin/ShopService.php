<?php

namespace App\Services\Admin;

use App\Repositories\Admin\ShopRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ShopService
{
    private $shopRepository;

    public function __construct(ShopRepositoryInterface $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    public function getShopByAdminId(string $shopAdminId)
    {
        $shop = $this->shopRepository->findByShopAdminId($shopAdminId);
        if (!$shop) {
            throw new \Exception('店舗情報が見つかりませんでした');
        }
        return $shop;
    }

    public function updateShopByAdminId(string $shopAdminId, array $data)
    {
        DB::beginTransaction();
        try {
            // 更新可能なフィールドのみを許可
            $allowedFields = [
                'name',
                'description', 
                'address',
                'latitude',
                'longitude',
                'open_time',
                'close_time',
                'image'
            ];

            $filteredData = array_intersect_key($data, array_flip($allowedFields));

            $shop = $this->shopRepository->updateByShopAdminId($shopAdminId, $filteredData);
            if (!$shop) {
                throw new \Exception('店舗情報の更新に失敗しました');
            }

            DB::commit();
            return $shop;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('店舗情報の更新に失敗しました: ' . $e->getMessage());
        }
    }
}