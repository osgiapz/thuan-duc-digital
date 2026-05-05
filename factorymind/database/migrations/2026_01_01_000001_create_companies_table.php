<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('parent_id')->nullable()->index();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_code', 20)->nullable();
            $table->string('company_type', 50)->default('subsidiary');
            $table->char('currency_code', 3)->default('VND');
            $table->smallInteger('fiscal_year_start')->default(1);
            $table->string('status', 20)->default('active')->index();
            $table->jsonb('address')->nullable();
            $table->jsonb('contact')->nullable();
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
        });

        // Self-referencing FK added after table is created
        Schema::table('companies', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
