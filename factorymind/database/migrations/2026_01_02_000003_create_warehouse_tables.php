<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('plant_id');
            $table->string('code', 20);
            $table->string('name');
            $table->string('warehouse_type', 50)->default('raw_material');
            // raw_material | wip | finished_goods | quarantine | scrap | transit
            $table->boolean('is_active')->default(true);
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('plant_id')->references('id')->on('plants');
            $table->unique(['plant_id', 'code']);
            $table->index(['company_id', 'plant_id', 'warehouse_type']);
        });

        Schema::create('warehouse_zones', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('warehouse_id');
            $table->string('code', 20);
            $table->string('name', 100);
            $table->string('zone_type', 30)->nullable();
            $table->timestamps();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            $table->unique(['warehouse_id', 'code']);
        });

        Schema::create('warehouse_racks', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('warehouse_id');
            $table->uuid('zone_id')->nullable();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->timestamps();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            $table->foreign('zone_id')->references('id')->on('warehouse_zones')->nullOnDelete();
            $table->unique(['warehouse_id', 'code']);
        });

        Schema::create('warehouse_bins', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('warehouse_id');
            $table->uuid('zone_id')->nullable();
            $table->uuid('rack_id')->nullable();
            $table->string('code', 30);
            $table->string('bin_type', 30)->nullable();
            $table->string('qr_code', 100)->unique()->nullable();
            $table->decimal('max_weight_kg', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            $table->foreign('zone_id')->references('id')->on('warehouse_zones')->nullOnDelete();
            $table->foreign('rack_id')->references('id')->on('warehouse_racks')->nullOnDelete();
            $table->unique(['warehouse_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_bins');
        Schema::dropIfExists('warehouse_racks');
        Schema::dropIfExists('warehouse_zones');
        Schema::dropIfExists('warehouses');
    }
};
