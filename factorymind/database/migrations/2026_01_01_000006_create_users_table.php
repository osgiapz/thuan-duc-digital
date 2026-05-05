<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password');
            $table->string('display_name');
            $table->string('avatar_url', 500)->nullable();
            $table->string('locale', 10)->default('vi');
            $table->string('timezone', 50)->default('Asia/Ho_Chi_Minh');
            $table->string('status', 20)->default('active');
            // active | inactive | suspended
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('user_work_contexts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id')->unique();
            $table->uuid('company_id');
            $table->uuid('plant_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->string('role_name', 100)->nullable();
            $table->string('context_label', 100)->nullable();
            $table->timestamp('switched_at')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('plant_id')->references('id')->on('plants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_work_contexts');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
