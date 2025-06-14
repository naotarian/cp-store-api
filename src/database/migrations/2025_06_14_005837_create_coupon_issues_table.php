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
        Schema::create('coupon_issues', function (Blueprint $table) {
            // Primary Key (ULID)
            $table->ulid('id')->primary();
            
            // 外部キー
            $table->foreignUlid('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->foreignUlid('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignUlid('schedule_id')->nullable()
                ->constrained('coupon_schedules')->onDelete('cascade')
                ->comment('生成元のスケジュールID（バッチ生成の場合）');
            
            // 発行タイプ
            $table->enum('issue_type', ['manual', 'batch_generated']);
            
            // 対象日と時間
            $table->date('target_date')->comment('対象日（バッチ処理で生成された日付）');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->time('start_time_only')->comment('開始時間（時刻のみ）');
            $table->time('end_time_only')->comment('終了時間（時刻のみ）');
            
            // 人数制限
            $table->integer('max_acquisitions')->nullable();
            $table->integer('current_acquisitions')->default(0);
            
            // ステータス
            $table->enum('status', ['active', 'expired', 'full', 'cancelled'])->default('active');
            $table->boolean('is_active')->default(true);
            
            // 発行者情報
            $table->foreignUlid('issued_by')->nullable()->constrained('shop_admins')->onDelete('set null');
            $table->timestamp('issued_at')->useCurrent();
            
            // タイムスタンプ
            $table->timestamps();
            
            // インデックス
            $table->index('status');
            $table->index(['start_time', 'end_time']);
            $table->index(['target_date', 'start_time_only', 'end_time_only'], 'idx_target_date_time_range');
            $table->index(['schedule_id', 'target_date'], 'idx_schedule_target_date');
            $table->index(['status', 'is_active', 'target_date'], 'idx_status_active_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_issues');
    }
};
