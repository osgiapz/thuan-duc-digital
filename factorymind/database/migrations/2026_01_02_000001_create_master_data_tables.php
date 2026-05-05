<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units_of_measure', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->string('code', 20);
            $table->string('name', 100);
            $table->string('uom_type', 30)->nullable();
            $table->boolean('is_base')->default(false);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unique(['company_id', 'code']);
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('plant_id')->nullable();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('crosses_midnight')->default(false);
            $table->smallInteger('break_minutes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('plant_id')->references('id')->on('plants')->nullOnDelete();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('parent_id')->nullable()->index();
            $table->string('code', 30);
            $table->string('name');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unique(['company_id', 'code']);
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('product_categories')->nullOnDelete();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('category_id')->nullable();
            $table->string('code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('product_type', 50)->default('finished_good');
            $table->string('base_uom', 20);
            $table->decimal('weight_kg', 18, 4)->nullable();
            $table->jsonb('dimensions')->nullable();
            $table->decimal('standard_cost', 20, 4)->default(0);
            $table->decimal('list_price', 20, 4)->default(0);
            $table->char('currency_code', 3)->default('VND');
            $table->smallInteger('lead_time_days')->default(0);
            $table->decimal('min_order_qty', 18, 4)->default(1);
            $table->decimal('reorder_point', 18, 4)->default(0);
            $table->decimal('safety_stock', 18, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->jsonb('attributes')->default('{}');
            $table->jsonb('meta')->default('{}');
            $table->uuid('created_by_id')->nullable();
            $table->uuid('updated_by_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('category_id')->references('id')->on('product_categories')->nullOnDelete();
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'product_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('units_of_measure');
    }
};
