<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupon_schedules', function (Blueprint $table) {
            // Primary Key (ULID)
            $table->ulid('id')->primary();
            
            // 外部キー
            $table->foreignUlid('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->foreignUlid('shop_id')->constrained('shops')->onDelete('cascade');
            
            // スケジュール基本情報
            $table->string('schedule_name', 255);
            
            // 曜日設定
            $table->enum('day_type', ['daily', 'weekdays', 'weekends', 'custom'])->default('daily');
            $table->json('custom_days')->nullable(); // [1,2,3,4,5] (月-金) など
            
            // 時間設定
            $table->time('start_time'); // 開始時間 (例: 10:00)
            $table->time('end_time');   // 終了時間 (例: 12:00)
            
            // 発行制限
            $table->integer('max_acquisitions')->nullable();
            
            // 有効期間
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            
            // ステータス管理
            $table->boolean('is_active')->default(true);
            
            // バッチ処理用
            $table->date('last_batch_processed_date')->nullable()
                ->comment('最後にバッチ処理された日付');
            
            // 作成者
            $table->foreignUlid('created_by')->nullable()->constrained('shop_admins')->onDelete('set null');
            
            // タイムスタンプ
            $table->timestamps();
            
            // インデックス
            $table->index(['shop_id', 'is_active'], 'idx_shop_active');
            $table->index('day_type', 'idx_day_type');
            $table->index(['is_active', 'valid_from', 'valid_until'], 'idx_active_valid_period');
            $table->index(['day_type', 'is_active'], 'idx_day_type_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_schedules');
    }
};
