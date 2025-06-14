<?php

namespace Tests\Unit\UseCases\Coupon;

use Tests\TestCase;
use App\UseCases\Coupon\DeleteCouponScheduleUseCase;
use App\Repositories\Coupon\CouponScheduleRepositoryInterface;
use App\Models\CouponSchedule;
use App\Models\ShopAdmin;
use Mockery;

class DeleteCouponScheduleUseCaseTest extends TestCase
{
    private DeleteCouponScheduleUseCase $useCase;
    private $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(CouponScheduleRepositoryInterface::class);
        $this->useCase = new DeleteCouponScheduleUseCase($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_スケジュールを削除できる()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $scheduleId = 'schedule-1';

        $mockSchedule = CouponSchedule::factory()->make([
            'id' => $scheduleId,
            'shop_id' => $admin->shop_id
        ]);

        $this->mockRepository
            ->shouldReceive('findByIdAndShop')
            ->with($scheduleId, $admin->shop_id)
            ->once()
            ->andReturn($mockSchedule);

        $this->mockRepository
            ->shouldReceive('delete')
            ->with($mockSchedule)
            ->once()
            ->andReturn(true);

        // 例外が発生しないことを確認
        $this->useCase->execute($admin, $scheduleId);
        $this->assertTrue(true); // テストが通ることを確認
    }

    public function test_存在しないスケジュールを削除しようとすると例外が発生する()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $scheduleId = 'non-existent-schedule';

        $this->mockRepository
            ->shouldReceive('findByIdAndShop')
            ->with($scheduleId, $admin->shop_id)
            ->once()
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('スケジュールが見つかりません');

        $this->useCase->execute($admin, $scheduleId);
    }

    public function test_他店舗のスケジュールを削除しようとすると例外が発生する()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $scheduleId = 'other-shop-schedule';

        // 他店舗のスケジュールなので、findByIdAndShopはnullを返す
        $this->mockRepository
            ->shouldReceive('findByIdAndShop')
            ->with($scheduleId, $admin->shop_id)
            ->once()
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('スケジュールが見つかりません');

        $this->useCase->execute($admin, $scheduleId);
    }
} 