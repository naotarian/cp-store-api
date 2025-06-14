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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // 既存のtokenable_idとtokenable_typeカラムを削除
            $table->dropMorphs('tokenable');
            
            // ULIDに対応したカラムを追加
            $table->string('tokenable_id', 26)->after('id'); // ULIDは26文字
            $table->string('tokenable_type')->after('tokenable_id');
            
            // インデックスを追加
            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // ULIDカラムを削除
            $table->dropIndex(['tokenable_type', 'tokenable_id']);
            $table->dropColumn(['tokenable_id', 'tokenable_type']);
            
            // 元のmorphsカラムを復元
            $table->morphs('tokenable');
        });
    }
}; 