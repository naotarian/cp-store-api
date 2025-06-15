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
        Schema::table('coupon_acquisitions', function (Blueprint $table) {
            $table->boolean('is_notification_read')->default(false)->after('usage_notes');
            $table->timestamp('notification_read_at')->nullable()->after('is_notification_read');
            $table->boolean('is_banner_shown')->default(false)->after('notification_read_at');
            $table->timestamp('banner_shown_at')->nullable()->after('is_banner_shown');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupon_acquisitions', function (Blueprint $table) {
            $table->dropColumn(['is_notification_read', 'notification_read_at', 'is_banner_shown', 'banner_shown_at']);
        });
    }
};
