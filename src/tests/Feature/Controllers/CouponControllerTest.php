<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\ShopAdmin;
use App\Models\Shop;
use App\Models\Coupon;
use App\Models\CouponSchedule;
use App\Models\CouponIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CouponControllerTest extends TestCase
{
    use RefreshDatabase;
    
    private ShopAdmin $admin;
    private Shop $shop;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->shop = Shop::factory()->create();
        $this->admin = ShopAdmin::factory()->create([
            'shop_id' => $this->shop->id
        ]);
        
        // Sanctumトークンを生成
        $this->token = $this->admin->createToken('test-token')->plainTextToken;
    }

    // ヘルパーメソッド：認証ヘッダー付きリクエスト
    private function authenticatedRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->json($method, $uri, $data, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
    }

    // ===== クーポン作成テスト =====

    public function test_認証なしでクーポン作成すると401エラーになる()
    {
        $response = $this->json('POST', '/admin/coupons', [
            'title' => 'テストクーポン',
            'description' => 'テスト用のクーポンです'
        ]);

        $response->assertStatus(401);
    }

    public function test_クーポンを正常に作成できる()
    {
        $couponData = [
            'title' => 'テストクーポン',
            'description' => 'テスト用のクーポンです',
            'conditions' => '店内利用のみ',
            'notes' => '備考欄',
            'image_url' => 'https://example.com/image.jpg'
        ];

        $response = $this->authenticatedRequest('POST', '/admin/coupons', $couponData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'coupon' => [
                            'id',
                            'title',
                            'description',
                            'conditions',
                            'notes',
                            'image_url',
                            'is_active',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'クーポンを作成しました',
                    'data' => [
                        'coupon' => [
                            'title' => 'テストクーポン',
                            'description' => 'テスト用のクーポンです',
                            'conditions' => '店内利用のみ',
                            'notes' => '備考欄',
                            'image_url' => 'https://example.com/image.jpg',
                            'is_active' => true
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('coupons', [
            'shop_id' => $this->shop->id,
            'title' => 'テストクーポン',
            'description' => 'テスト用のクーポンです'
        ]);
    }

    public function test_必須フィールドなしでクーポン作成すると422エラーになる()
    {
        $response = $this->authenticatedRequest('POST', '/admin/coupons', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
    }

    public function test_タイトルが長すぎるとクーポン作成で422エラーになる()
    {
        $response = $this->authenticatedRequest('POST', '/admin/coupons', [
            'title' => str_repeat('あ', 256), // 255文字を超える
            'description' => 'テスト用のクーポンです'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
    }

    // ===== クーポン更新テスト =====

    public function test_認証なしでクーポン更新すると401エラーになる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);

        $response = $this->json('PUT', "/admin/coupons/{$coupon->id}", [
            'title' => '更新されたクーポン'
        ]);

        $response->assertStatus(401);
    }

    public function test_クーポンを正常に更新できる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);

        $updateData = [
            'title' => '更新されたクーポン',
            'description' => '更新された説明',
            'conditions' => '更新された条件',
            'notes' => '更新された備考'
        ];

        $response = $this->authenticatedRequest('PUT', "/admin/coupons/{$coupon->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'クーポンを更新しました',
                    'data' => [
                        'coupon' => [
                            'id' => $coupon->id,
                            'title' => '更新されたクーポン',
                            'description' => '更新された説明',
                            'conditions' => '更新された条件',
                            'notes' => '更新された備考'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'title' => '更新されたクーポン'
        ]);
    }

    public function test_存在しないクーポンを更新すると404エラーになる()
    {
        $response = $this->authenticatedRequest('PUT', '/admin/coupons/non-existent-id', [
            'title' => '更新されたクーポン'
        ]);

        $response->assertStatus(404);
    }

    public function test_他店舗のクーポンを更新すると404エラーになる()
    {
        $otherShop = Shop::factory()->create();
        $otherCoupon = Coupon::factory()->create(['shop_id' => $otherShop->id]);

        $response = $this->authenticatedRequest('PUT', "/admin/coupons/{$otherCoupon->id}", [
            'title' => '更新されたクーポン'
        ]);

        $response->assertStatus(404);
    }

    // ===== クーポン一覧取得テスト =====

    public function test_認証なしでクーポン一覧取得すると401エラーになる()
    {
        $response = $this->json('GET', '/admin/coupons');

        $response->assertStatus(401);
    }

    public function test_クーポン一覧を正常に取得できる()
    {
        Coupon::factory()->count(3)->create(['shop_id' => $this->shop->id]);
        // 他店舗のクーポンも作成
        $otherShop = Shop::factory()->create();
        Coupon::factory()->count(2)->create(['shop_id' => $otherShop->id]);

        $response = $this->authenticatedRequest('GET', '/admin/coupons');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'coupons' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'conditions',
                                'notes',
                                'image_url',
                                'is_active',
                                'created_at',
                                'updated_at',
                                'active_issues_count',
                                'schedules_count',
                                'total_issues_count'
                            ]
                        ]
                    ]
                ]);

        // 自店舗のクーポンのみが返されることを確認
        $coupons = $response->json('data.coupons');
        $this->assertCount(3, $coupons);
        foreach ($coupons as $coupon) {
            $this->assertDatabaseHas('coupons', [
                'id' => $coupon['id'],
                'shop_id' => $this->shop->id
            ]);
        }
    }

    // ===== 発行中クーポン取得テスト =====

    public function test_認証なしで発行中クーポン取得すると401エラーになる()
    {
        $response = $this->json('GET', '/admin/coupons/active-issues');

        $response->assertStatus(401);
    }

    public function test_発行中クーポンを正常に取得できる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        
        // 発行中のクーポンを作成
        CouponIssue::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id,
            'status' => 'active',
            'is_active' => true,
            'start_datetime' => now()->subHour(),
            'end_datetime' => now()->addHour()
        ]);

        // 期限切れのクーポンも作成（これは含まれない）
        CouponIssue::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id,
            'status' => 'active',
            'is_active' => true,
            'start_datetime' => now()->subHours(3),
            'end_datetime' => now()->subHour()
        ]);

        $response = $this->authenticatedRequest('GET', '/admin/coupons/active-issues');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'active_issues' => [
                            '*' => [
                                'id',
                                'coupon_id',
                                'issue_type',
                                'start_datetime',
                                'end_datetime',
                                'duration_minutes',
                                'max_acquisitions',
                                'current_acquisitions',
                                'remaining_count',
                                'time_remaining',
                                'is_available',
                                'status',
                                'issued_at',
                                'coupon' => [
                                    'id',
                                    'title',
                                    'description',
                                    'conditions',
                                    'notes'
                                ]
                            ]
                        ]
                    ]
                ]);

        // 発行中のクーポンが1件返されることを確認
        $activeIssues = $response->json('data.active_issues');
        $this->assertCount(1, $activeIssues);
    }

    // ===== スケジュール関連テスト =====

    public function test_認証なしでスケジュール一覧取得すると401エラーになる()
    {
        $response = $this->json('GET', '/admin/coupons/schedules');

        $response->assertStatus(401);
    }

    public function test_スケジュール一覧を正常に取得できる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        CouponSchedule::factory()->count(2)->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id
        ]);

        $response = $this->authenticatedRequest('GET', '/admin/coupons/schedules');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'schedules' => [
                            '*' => [
                                'id',
                                'coupon_id',
                                'schedule_name',
                                'day_type',
                                'day_type_display',
                                'custom_days',
                                'start_time',
                                'end_time',
                                'time_range_display',
                                'duration_minutes',
                                'max_acquisitions',
                                'valid_from',
                                'valid_until',
                                'is_active',
                                'last_batch_processed_date',
                                'coupon' => [
                                    'id',
                                    'title',
                                    'description',
                                    'conditions',
                                    'notes'
                                ]
                            ]
                        ]
                    ]
                ]);

        $schedules = $response->json('data.schedules');
        $this->assertCount(2, $schedules);
    }

    public function test_認証なしでスケジュール作成すると401エラーになる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);

        $response = $this->json('POST', '/admin/coupons/schedules', [
            'coupon_id' => $coupon->id,
            'schedule_name' => 'テストスケジュール',
            'day_type' => 'daily',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'duration_minutes' => 120,
            'valid_from' => now()->format('Y-m-d')
        ]);

        $response->assertStatus(401);
    }

    public function test_スケジュールを正常に作成できる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);

        $scheduleData = [
            'coupon_id' => $coupon->id,
            'schedule_name' => 'テストスケジュール',
            'day_type' => 'daily',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'duration_minutes' => 120,
            'valid_from' => now()->format('Y-m-d')
        ];

        $response = $this->authenticatedRequest('POST', '/admin/coupons/schedules', $scheduleData);

        $response->assertStatus(201)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'スケジュールを作成しました'
                ]);

        $this->assertDatabaseHas('coupon_schedules', [
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id,
            'schedule_name' => 'テストスケジュール',
            'day_type' => 'daily'
        ]);
    }

    public function test_認証なしでスケジュール更新すると401エラーになる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        $schedule = CouponSchedule::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id
        ]);

        $response = $this->json('PUT', "/admin/coupons/schedules/{$schedule->id}", [
            'schedule_name' => '更新されたスケジュール'
        ]);

        $response->assertStatus(401);
    }

    public function test_スケジュールを正常に更新できる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        $schedule = CouponSchedule::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id
        ]);

        $updateData = [
            'coupon_id' => $coupon->id,
            'schedule_name' => '更新されたスケジュール',
            'day_type' => 'weekdays',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'duration_minutes' => 120,
            'valid_from' => now()->format('Y-m-d'),
            'is_active' => true
        ];

        $response = $this->authenticatedRequest('PUT', "/admin/coupons/schedules/{$schedule->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'スケジュールを更新しました'
                ]);

        $this->assertDatabaseHas('coupon_schedules', [
            'id' => $schedule->id,
            'schedule_name' => '更新されたスケジュール',
            'day_type' => 'weekdays'
        ]);
    }

    public function test_認証なしでスケジュール削除すると401エラーになる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        $schedule = CouponSchedule::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id
        ]);

        $response = $this->json('DELETE', "/admin/coupons/schedules/{$schedule->id}");

        $response->assertStatus(401);
    }

    public function test_スケジュールを正常に削除できる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        $schedule = CouponSchedule::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id
        ]);

        $response = $this->authenticatedRequest('DELETE', "/admin/coupons/schedules/{$schedule->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'スケジュールを削除しました'
                ]);

        $this->assertDatabaseMissing('coupon_schedules', [
            'id' => $schedule->id
        ]);
    }

    public function test_認証なしでスケジュールステータス切り替えすると401エラーになる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        $schedule = CouponSchedule::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id,
            'is_active' => true
        ]);

        $response = $this->json('PATCH', "/admin/coupons/schedules/{$schedule->id}/toggle-status");

        $response->assertStatus(401);
    }

    public function test_スケジュールステータスを正常に切り替えできる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        $schedule = CouponSchedule::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id,
            'is_active' => true
        ]);

        $response = $this->authenticatedRequest('PATCH', "/admin/coupons/schedules/{$schedule->id}/toggle-status");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'スケジュールを無効にしました',
                    'data' => [
                        'schedule' => [
                            'id' => $schedule->id,
                            'is_active' => false
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('coupon_schedules', [
            'id' => $schedule->id,
            'is_active' => false
        ]);
    }

    // ===== 即座発行テスト =====

    public function test_認証なしでクーポン即座発行すると401エラーになる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);

        $response = $this->json('POST', "/admin/coupons/{$coupon->id}/issue-now", [
            'duration_minutes' => 60
        ]);

        $response->assertStatus(401);
    }

    public function test_クーポンを即座に発行できる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);

        $response = $this->authenticatedRequest('POST', "/admin/coupons/{$coupon->id}/issue-now", [
            'duration_minutes' => 60,
            'max_acquisitions' => 100
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'issue_id',
                        'end_time'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'クーポンを発行しました'
                ]);

        $this->assertDatabaseHas('coupon_issues', [
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id,
            'issue_type' => 'manual',
            'max_acquisitions' => 100
        ]);
    }

    // ===== 発行停止テスト =====

    public function test_認証なしでクーポン発行停止すると401エラーになる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        $issue = CouponIssue::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id,
            'status' => 'active'
        ]);

        $response = $this->json('POST', "/admin/coupons/issues/{$issue->id}/stop");

        $response->assertStatus(401);
    }

    public function test_クーポン発行を正常に停止できる()
    {
        $coupon = Coupon::factory()->create(['shop_id' => $this->shop->id]);
        $issue = CouponIssue::factory()->create([
            'coupon_id' => $coupon->id,
            'shop_id' => $this->shop->id,
            'status' => 'active',
            'is_active' => true
        ]);

        $response = $this->authenticatedRequest('POST', "/admin/coupons/issues/{$issue->id}/stop");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'クーポン発行を停止しました'
                ]);

        $this->assertDatabaseHas('coupon_issues', [
            'id' => $issue->id,
            'status' => 'cancelled',
            'is_active' => false
        ]);
    }
} 