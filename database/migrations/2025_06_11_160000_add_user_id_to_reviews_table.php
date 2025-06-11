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
        Schema::table('reviews', function (Blueprint $table) {
            // user_idカラムを追加
            $table->ulid('user_id')->after('shop_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // user_nameカラムを削除
            $table->dropColumn('user_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // user_idカラムを削除
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            
            // user_nameカラムを復元
            $table->string('user_name')->after('shop_id');
        });
    }
}; 