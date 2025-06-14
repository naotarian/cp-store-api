<?php

namespace Tests\Unit\UseCases\Coupon;

use Tests\TestCase;
use App\UseCases\Coupon\ToggleCouponScheduleStatusUseCase;
use App\Repositories\Coupon\CouponScheduleRepositoryInterface;
use App\Models\CouponSchedule;
use App\Models\ShopAdmin;
use Mockery;

class ToggleCouponScheduleStatusUseCaseTest extends TestCase
{
    private ToggleCouponScheduleStatusUseCase $useCase;
    private $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(CouponScheduleRepositoryInterface::class);
        $this->useCase = new ToggleCouponScheduleStatusUseCase($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_アクティブなスケジュールを非アクティブにできる()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $scheduleId = 'schedule-1';

        $mockSchedule = CouponSchedule::factory()->make([
            'id' => $scheduleId,
            'shop_id' => $admin->shop_id,
            'is_active' => true
        ]);

        $updatedSchedule = CouponSchedule::factory()->make([
            'id' => $scheduleId,
            'shop_id' => $admin->shop_id,
            'is_active' => false
        ]);

        $this->mockRepository
            ->shouldReceive('findByIdAndShop')
            ->with($scheduleId, $admin->shop_id)
            ->once()
            ->andReturn($mockSchedule);

        $this->mockRepository
            ->shouldReceive('toggleStatus')
            ->with($mockSchedule)
            ->once()
            ->andReturn($updatedSchedule);

        $result = $this->useCase->execute($admin, $scheduleId);

        $this->assertFalse($result->is_active);
    }

    public function test_非アクティブなスケジュールをアクティブにできる()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $scheduleId = 'schedule-1';

        $mockSchedule = CouponSchedule::factory()->make([
            'id' => $scheduleId,
            'shop_id' => $admin->shop_id,
            'is_active' => false
        ]);

        $updatedSchedule = CouponSchedule::factory()->make([
            'id' => $scheduleId,
            'shop_id' => $admin->shop_id,
            'is_active' => true
        ]);

        $this->mockRepository
            ->shouldReceive('findByIdAndShop')
            ->with($scheduleId, $admin->shop_id)
            ->once()
            ->andReturn($mockSchedule);

        $this->mockRepository
            ->shouldReceive('toggleStatus')
            ->with($mockSchedule)
            ->once()
            ->andReturn($updatedSchedule);

        $result = $this->useCase->execute($admin, $scheduleId);

        $this->assertTrue($result->is_active);
    }

    public function test_存在しないスケジュールのステータス切り替えで例外が発生する()
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

    public function test_他店舗のスケジュールのステータス切り替えで例外が発生する()
    {
        $admin = ShopAdmin::factory()->make(['shop_id' => 'shop-1']);
        $scheduleId = 'other-shop-schedule';

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