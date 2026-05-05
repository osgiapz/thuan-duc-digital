<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('plant_id')->nullable();
            $table->uuid('parent_id')->nullable()->index();
            $table->string('code', 20);
            $table->string('name');
            $table->string('dept_type', 50)->nullable();
            $table->uuid('head_user_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('plant_id')->references('id')->on('plants')->nullOnDelete();
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'plant_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
