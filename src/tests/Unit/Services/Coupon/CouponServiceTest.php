<?php

namespace Tests\Unit\Services\Coupon;

use Tests\TestCase;
use App\Services\Coupon\CouponService;
use App\Repositories\Coupon\CouponRepositoryInterface;
use App\Models\Coupon;
use App\Models\ShopAdmin;
use Mockery;

class CouponServiceTest extends TestCase
{
    private CouponService $service;
    private $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(CouponRepositoryInterface::class);
        $this->service = new CouponService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_クーポンを作成できる()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $couponData = [
            'title' => 'テストクーポン',
            'description' => 'テスト用のクーポンです',
            'conditions' => '店内利用のみ',
            'notes' => '備考欄',
            'image_url' => 'https://example.com/image.jpg'
        ];

        $expectedCoupon = Coupon::factory()->make(array_merge($couponData, [
            'id' => 'coupon-1',
            'shop_id' => 'shop-1'
        ]));

        $this->mockRepository
            ->shouldReceive('createCoupon')
            ->with(array_merge($couponData, [
                'shop_id' => $admin->shop_id,
                'is_active' => true
            ]))
            ->once()
            ->andReturn($expectedCoupon);

        $result = $this->service->createCoupon($admin, $couponData);

        $this->assertEquals($expectedCoupon, $result);
    }

    public function test_クーポンを更新できる()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $couponId = 'coupon-1';
        $updateData = [
            'title' => '更新されたクーポン',
            'description' => '更新された説明'
        ];

        $existingCoupon = Coupon::factory()->make([
            'id' => $couponId,
            'shop_id' => 'shop-1',
            'title' => '元のクーポン'
        ]);

        $updatedCoupon = Coupon::factory()->make(array_merge($updateData, [
            'id' => $couponId,
            'shop_id' => 'shop-1'
        ]));

        $this->mockRepository
            ->shouldReceive('findCouponById')
            ->with($couponId)
            ->once()
            ->andReturn($existingCoupon);

        $this->mockRepository
            ->shouldReceive('updateCoupon')
            ->with($couponId, $updateData)
            ->once()
            ->andReturn($updatedCoupon);

        $result = $this->service->updateCoupon($admin, $couponId, $updateData);

        $this->assertEquals($updatedCoupon, $result);
    }

    public function test_存在しないクーポンを更新しようとすると例外が発生する()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $couponId = 'non-existent-coupon';
        $updateData = ['title' => '更新されたクーポン'];

        $this->mockRepository
            ->shouldReceive('findCouponById')
            ->with($couponId)
            ->once()
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('クーポンが見つかりません');

        $this->service->updateCoupon($admin, $couponId, $updateData);
    }

    public function test_バリデーションエラーでクーポン作成が失敗する()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $invalidData = [
            'title' => str_repeat('あ', 256), // 255文字を超える
            'description' => 'テスト用のクーポンです'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('クーポン名は255文字以内で入力してください');

        $this->service->createCoupon($admin, $invalidData);
    }

    public function test_バリデーションエラーでクーポン更新が失敗する()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $couponId = 'coupon-1';
        $invalidData = [
            'image_url' => str_repeat('a', 501) // 500文字を超える
        ];

        $existingCoupon = Coupon::factory()->make([
            'id' => $couponId,
            'shop_id' => 'shop-1'
        ]);

        $this->mockRepository
            ->shouldReceive('findCouponById')
            ->with($couponId)
            ->once()
            ->andReturn($existingCoupon);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('画像URLは500文字以内で入力してください');

        $this->service->updateCoupon($admin, $couponId, $invalidData);
    }

    public function test_データ検証が正常に動作する()
    {
        $validData = [
            'title' => 'テストクーポン',
            'description' => 'テスト用のクーポンです',
            'conditions' => '店内利用のみ',
            'notes' => '備考欄',
            'image_url' => 'https://example.com/image.jpg'
        ];

        // プライベートメソッドをテストするためにリフレクションを使用
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateCouponData');
        $method->setAccessible(true);

        // 例外が発生しないことを確認
        $method->invoke($this->service, $validData);
        $this->assertTrue(true); // テストが通ることを確認
    }
} 