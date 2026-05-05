<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshops', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('plant_id');
            $table->uuid('department_id')->nullable();
            $table->string('code', 20);
            $table->string('name');
            $table->uuid('supervisor_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('plant_id')->references('id')->on('plants');
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->unique(['plant_id', 'code']);
            $table->index(['company_id', 'plant_id']);
        });

        Schema::create('production_lines', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('plant_id');
            $table->uuid('workshop_id')->nullable();
            $table->string('code', 20);
            $table->string('name');
            $table->string('line_type', 50)->nullable();
            $table->decimal('capacity_per_hour', 18, 4)->nullable();
            $table->string('capacity_uom', 20)->nullable();
            $table->string('status', 20)->default('active');
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('plant_id')->references('id')->on('plants');
            $table->foreign('workshop_id')->references('id')->on('workshops')->nullOnDelete();
            $table->unique(['plant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_lines');
        Schema::dropIfExists('workshops');
    }
};
