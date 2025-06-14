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
            
            // 開始・終了日時
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            
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
            $table->index(['start_datetime', 'end_datetime']);
            $table->index(['schedule_id', 'start_datetime'], 'idx_schedule_start_datetime');
            $table->index(['status', 'is_active', 'start_datetime'], 'idx_status_active_datetime');
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
