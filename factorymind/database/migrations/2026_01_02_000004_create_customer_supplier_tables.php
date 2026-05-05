<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->string('code', 30);
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_code', 20)->nullable();
            $table->string('customer_type', 30)->default('domestic');
            // domestic | export | b2b | b2c
            $table->string('credit_limit_currency', 3)->default('VND');
            $table->decimal('credit_limit', 20, 4)->default(0);
            $table->smallInteger('payment_days')->default(30);
            $table->string('status', 20)->default('active');
            $table->jsonb('billing_address')->nullable();
            $table->jsonb('shipping_address')->nullable();
            $table->jsonb('meta')->default('{}');
            $table->uuid('created_by_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status', 'customer_type']);
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('company_id');
            $table->string('code', 30);
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_code', 20)->nullable();
            $table->string('supplier_type', 30)->default('material');
            // material | service | subcontract | utility
            $table->smallInteger('lead_time_days')->default(7);
            $table->smallInteger('payment_days')->default(30);
            $table->string('status', 20)->default('active');
            $table->jsonb('address')->nullable();
            $table->jsonb('meta')->default('{}');
            $table->uuid('created_by_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};
