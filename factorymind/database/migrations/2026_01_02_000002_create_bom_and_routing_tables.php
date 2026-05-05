<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── BOMs ───────────────────────────────────────────────────
        Schema::create('boms', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('product_id');
            $table->string('code', 50);
            $table->string('name')->nullable();
            $table->string('version', 20)->default('1.0');
            $table->string('uom', 20);
            $table->decimal('quantity', 18, 4)->default(1);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->uuid('created_by_id')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unique(['company_id', 'code', 'version']);
            $table->index(['product_id', 'is_active']);
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('bom_id');
            $table->uuid('company_id');
            $table->smallInteger('sequence')->default(10);
            $table->uuid('material_id');
            $table->decimal('quantity', 18, 4);
            $table->string('uom', 20);
            $table->decimal('scrap_pct', 8, 4)->default(0);
            $table->boolean('is_phantom')->default(false);
            $table->string('operation_step', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('bom_id')->references('id')->on('boms')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('material_id')->references('id')->on('products');
            $table->index(['bom_id', 'sequence']);
        });

        // ── Routings ───────────────────────────────────────────────
        Schema::create('routings', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->uuid('product_id');
            $table->string('code', 50);
            $table->string('name')->nullable();
            $table->string('version', 20)->default('1.0');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unique(['company_id', 'code', 'version']);
        });

        Schema::create('routing_steps', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('routing_id');
            $table->uuid('company_id');
            $table->smallInteger('step_number');
            $table->string('name');
            $table->string('operation_code', 50)->nullable();
            $table->uuid('workshop_id')->nullable();
            $table->uuid('line_id')->nullable();
            $table->uuid('machine_category_id')->nullable();
            $table->decimal('std_time_minutes', 10, 4)->nullable();
            $table->decimal('setup_time_minutes', 10, 4)->nullable();
            $table->smallInteger('labor_count')->default(1);
            $table->uuid('output_product_id')->nullable();
            $table->decimal('yield_pct', 8, 4)->default(100);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('routing_id')->references('id')->on('routings')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('workshop_id')->references('id')->on('workshops')->nullOnDelete();
            $table->foreign('line_id')->references('id')->on('production_lines')->nullOnDelete();
            $table->foreign('machine_category_id')->references('id')->on('machine_categories')->nullOnDelete();
            $table->foreign('output_product_id')->references('id')->on('products')->nullOnDelete();
            $table->unique(['routing_id', 'step_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_steps');
        Schema::dropIfExists('routings');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('boms');
    }
};
