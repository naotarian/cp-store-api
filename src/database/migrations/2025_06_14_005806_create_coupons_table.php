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
        Schema::create('coupons', function (Blueprint $table) {
            // Primary Key (ULID)
            $table->ulid('id')->primary();
            
            // 外部キー
            $table->foreignUlid('shop_id')->constrained('shops')->onDelete('cascade');
            
            // 基本情報
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_url', 500)->nullable();
            
            // ステータス
            $table->boolean('is_active')->default(true);
            
            // タイムスタンプ
            $table->timestamps();
            
            // インデックス
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
