<?php

namespace Tests\Unit\UseCases\Coupon;

use Tests\TestCase;
use App\UseCases\Coupon\UpdateCouponUseCase;
use App\Services\Coupon\CouponService;
use App\Models\Coupon;
use App\Models\ShopAdmin;
use Mockery;

class UpdateCouponUseCaseTest extends TestCase
{
    private UpdateCouponUseCase $useCase;
    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockService = Mockery::mock(CouponService::class);
        $this->useCase = new UpdateCouponUseCase($this->mockService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_クーポンを更新できる()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $couponId = 'coupon-1';
        $updateData = [
            'title' => '更新されたクーポン',
            'description' => '更新された説明'
        ];

        $expectedCoupon = Coupon::factory()->make(array_merge($updateData, [
            'id' => $couponId,
            'shop_id' => 'shop-1'
        ]));

        $this->mockService
            ->shouldReceive('updateCoupon')
            ->with($admin, $couponId, $updateData)
            ->once()
            ->andReturn($expectedCoupon);

        $result = $this->useCase->execute($admin, $couponId, $updateData);

        $this->assertEquals($expectedCoupon, $result);
    }

    public function test_サービスで例外が発生した場合は例外を再スローする()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $couponId = 'coupon-1';
        $updateData = ['title' => '更新されたクーポン'];

        $this->mockService
            ->shouldReceive('updateCoupon')
            ->with($admin, $couponId, $updateData)
            ->once()
            ->andThrow(new \Exception('クーポン更新に失敗しました'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('クーポン更新に失敗しました');

        $this->useCase->execute($admin, $couponId, $updateData);
    }
} 