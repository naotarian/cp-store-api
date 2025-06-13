<?php

namespace App\Http\Controllers;

use App\UseCases\Shop\GetAllShopsUseCase;
use App\UseCases\Shop\GetShopByIdUseCase;
use App\UseCases\Shop\CreateShopUseCase;
use App\UseCases\Shop\UpdateShopUseCase;
use App\UseCases\Shop\DeleteShopUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    private $getAllShopsUseCase;
    private $getShopByIdUseCase;
    private $createShopUseCase;
    private $updateShopUseCase;
    private $deleteShopUseCase;

    public function __construct(
        GetAllShopsUseCase $getAllShopsUseCase,
        GetShopByIdUseCase $getShopByIdUseCase,
        CreateShopUseCase $createShopUseCase,
        UpdateShopUseCase $updateShopUseCase,
        DeleteShopUseCase $deleteShopUseCase
    ) {
        $this->getAllShopsUseCase = $getAllShopsUseCase;
        $this->getShopByIdUseCase = $getShopByIdUseCase;
        $this->createShopUseCase = $createShopUseCase;
        $this->updateShopUseCase = $updateShopUseCase;
        $this->deleteShopUseCase = $deleteShopUseCase;
    }

    /**
     * Display a listing of the shops.
     */
    public function index(): JsonResponse
    {
        try {
            $shops = $this->getAllShopsUseCase->execute();
            
            return response()->json([
                'status' => 'success',
                'data' => $shops
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created shop in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|string',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            $shop = $this->createShopUseCase->execute($validated);

            return response()->json([
                'status' => 'success',
                'data' => $shop
            ], 201)->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified shop.
     */
    public function show(string $shop): JsonResponse
    {
        try {
            $shopData = $this->getShopByIdUseCase->execute($shop);
            
            return response()->json([
                'status' => 'success',
                'data' => $shopData
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified shop in storage.
     */
    public function update(Request $request, string $shop): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'image' => 'string',
            'open_time' => 'date_format:H:i',
            'close_time' => 'date_format:H:i',
            'address' => 'string',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180',
        ]);

        try {
            $updatedShop = $this->updateShopUseCase->execute($shop, $validated);

            return response()->json([
                'status' => 'success',
                'data' => $updatedShop
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified shop from storage.
     */
    public function destroy(string $shop): JsonResponse
    {
        try {
            $this->deleteShopUseCase->execute($shop);

            return response()->json([
                'status' => 'success',
                'message' => 'Shop deleted successfully'
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
