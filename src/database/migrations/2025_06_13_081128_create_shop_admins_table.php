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
        Schema::create('shop_admins', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['root', 'manager', 'staff'])->default('staff');
            $table->boolean('is_active')->default(true);
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // インデックス
            $table->index(['shop_id', 'email']);
            $table->index(['shop_id', 'role']);
            $table->index(['shop_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_admins');
    }
};
