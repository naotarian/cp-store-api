<?php

namespace Tests\Unit\UseCases\Coupon;

use Tests\TestCase;
use App\UseCases\Coupon\CreateCouponUseCase;
use App\Services\Coupon\CouponService;
use App\Models\Coupon;
use App\Models\ShopAdmin;
use Mockery;

class CreateCouponUseCaseTest extends TestCase
{
    private CreateCouponUseCase $useCase;
    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockService = Mockery::mock(CouponService::class);
        $this->useCase = new CreateCouponUseCase($this->mockService);
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

        $this->mockService
            ->shouldReceive('createCoupon')
            ->with($admin, $couponData)
            ->once()
            ->andReturn($expectedCoupon);

        $result = $this->useCase->execute($admin, $couponData);

        $this->assertEquals($expectedCoupon, $result);
    }

    public function test_サービスで例外が発生した場合は例外を再スローする()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $couponData = [
            'title' => 'テストクーポン'
        ];

        $this->mockService
            ->shouldReceive('createCoupon')
            ->with($admin, $couponData)
            ->once()
            ->andThrow(new \Exception('クーポン作成に失敗しました'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('クーポン作成に失敗しました');

        $this->useCase->execute($admin, $couponData);
    }
} 