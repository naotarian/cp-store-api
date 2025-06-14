<?php

namespace Tests\Unit\Repositories\Coupon;

use Tests\TestCase;
use App\Repositories\Coupon\CouponRepositoryInterface;
use App\Models\Coupon;
use App\Models\Shop;
use App\Models\ShopAdmin;
use Mockery;

class CouponRepositoryTest extends TestCase
{
    private $repository;
    private Shop $shop;
    private ShopAdmin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(CouponRepositoryInterface::class);
        $this->shop = Shop::factory()->make(['id' => 'shop-1']);
        $this->admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_クーポンを作成できる()
    {
        $couponData = [
            'shop_id' => $this->shop->id,
            'created_by' => $this->admin->id,
            'title' => 'テストクーポン',
            'description' => 'テスト用のクーポンです',
            'conditions' => '店内利用のみ',
            'notes' => '備考欄',
            'image_url' => 'https://example.com/image.jpg',
            'is_active' => true
        ];

        $expectedCoupon = Coupon::factory()->make($couponData);

        $this->repository
            ->shouldReceive('create')
            ->with($couponData)
            ->once()
            ->andReturn($expectedCoupon);

        $coupon = $this->repository->create($couponData);

        $this->assertInstanceOf(Coupon::class, $coupon);
        $this->assertEquals($couponData['title'], $coupon->title);
    }

    public function test_クーポンを更新できる()
    {
        $coupon = Coupon::factory()->make([
            'shop_id' => $this->shop->id,
            'title' => '元のタイトル'
        ]);

        $updateData = [
            'title' => '更新されたタイトル',
            'description' => '更新された説明'
        ];

        $updatedCoupon = Coupon::factory()->make(array_merge($updateData, [
            'shop_id' => $this->shop->id
        ]));

        $this->repository
            ->shouldReceive('update')
            ->with($coupon, $updateData)
            ->once()
            ->andReturn($updatedCoupon);

        $result = $this->repository->update($coupon, $updateData);

        $this->assertEquals('更新されたタイトル', $result->title);
        $this->assertEquals('更新された説明', $result->description);
    }

    public function test_店舗IDでクーポンを取得できる()
    {
        $coupons = collect([
            Coupon::factory()->make(['shop_id' => $this->shop->id]),
            Coupon::factory()->make(['shop_id' => $this->shop->id]),
            Coupon::factory()->make(['shop_id' => $this->shop->id])
        ]);

        $this->repository
            ->shouldReceive('findByShopId')
            ->with($this->shop->id)
            ->once()
            ->andReturn($coupons);

        $result = $this->repository->findByShopId($this->shop->id);

        $this->assertCount(3, $result);
    }

    public function test_IDと店舗IDでクーポンを取得できる()
    {
        $coupon = Coupon::factory()->make(['shop_id' => $this->shop->id]);

        $this->repository
            ->shouldReceive('findByIdAndShop')
            ->with($coupon->id, $this->shop->id)
            ->once()
            ->andReturn($coupon);

        $foundCoupon = $this->repository->findByIdAndShop($coupon->id, $this->shop->id);

        $this->assertNotNull($foundCoupon);
        $this->assertEquals($coupon->id, $foundCoupon->id);
    }

    public function test_存在しないIDでクーポンを取得するとnullが返される()
    {
        $this->repository
            ->shouldReceive('findByIdAndShop')
            ->with('non-existent-id', $this->shop->id)
            ->once()
            ->andReturn(null);

        $foundCoupon = $this->repository->findByIdAndShop('non-existent-id', $this->shop->id);

        $this->assertNull($foundCoupon);
    }

    public function test_他店舗のクーポンを取得するとnullが返される()
    {
        $this->repository
            ->shouldReceive('findByIdAndShop')
            ->with('other-coupon-id', $this->shop->id)
            ->once()
            ->andReturn(null);

        $foundCoupon = $this->repository->findByIdAndShop('other-coupon-id', $this->shop->id);

        $this->assertNull($foundCoupon);
    }

    public function test_クーポンを削除できる()
    {
        $coupon = Coupon::factory()->make(['shop_id' => $this->shop->id]);

        $this->repository
            ->shouldReceive('delete')
            ->with($coupon)
            ->once()
            ->andReturn(true);

        $result = $this->repository->delete($coupon);

        $this->assertTrue($result);
    }
} 