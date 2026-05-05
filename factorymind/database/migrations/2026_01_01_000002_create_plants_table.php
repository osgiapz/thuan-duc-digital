<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plants', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->string('code', 20);
            $table->string('name');
            $table->string('plant_type', 50)->nullable();
            // manufacturing | warehouse | office | distribution
            $table->uuid('manager_user_id')->nullable();
            $table->jsonb('address')->nullable();
            $table->jsonb('coordinates')->nullable();
            // {"lat": 10.123, "lng": 106.456}
            $table->string('status', 20)->default('active');
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
