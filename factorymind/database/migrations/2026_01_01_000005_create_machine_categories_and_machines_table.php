<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_categories', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->string('code', 20);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->unique(['company_id', 'code']);
        });

        Schema::create('machines', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('plant_id');
            $table->uuid('workshop_id')->nullable();
            $table->uuid('line_id')->nullable();
            $table->uuid('machine_category_id')->nullable();
            $table->string('code', 20);
            $table->string('name');
            $table->string('serial_number', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('theoretical_capacity', 18, 4)->nullable();
            $table->string('capacity_uom', 20)->nullable();
            $table->string('status', 20)->default('active');
            // active | maintenance | breakdown | retired
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('plant_id')->references('id')->on('plants');
            $table->foreign('workshop_id')->references('id')->on('workshops')->nullOnDelete();
            $table->foreign('line_id')->references('id')->on('production_lines')->nullOnDelete();
            $table->foreign('machine_category_id')->references('id')->on('machine_categories')->nullOnDelete();
            $table->unique(['plant_id', 'code']);
            $table->index(['company_id', 'plant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
        Schema::dropIfExists('machine_categories');
    }
};
