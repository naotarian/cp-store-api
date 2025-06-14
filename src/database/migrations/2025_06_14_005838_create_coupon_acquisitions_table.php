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
        Schema::create('coupon_acquisitions', function (Blueprint $table) {
            // Primary Key (ULID)
            $table->ulid('id')->primary();
            
            // 外部キー
            $table->foreignUlid('coupon_issue_id')->constrained('coupon_issues')->onDelete('cascade');
            $table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
            
            // 取得・使用状況
            $table->timestamp('acquired_at')->useCurrent();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expired_at');
            
            // ステータス
            $table->enum('status', ['active', 'used', 'expired'])->default('active');
            
            // 使用時の詳細
            $table->foreignUlid('processed_by')->nullable()->constrained('shop_admins')->onDelete('set null');
            $table->text('usage_notes')->nullable();
            
            // タイムスタンプ
            $table->timestamps();
            
            // インデックス・制約
            $table->unique(['user_id', 'coupon_issue_id'], 'unique_user_issue');
            $table->index('status');
            $table->index('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_acquisitions');
    }
};
