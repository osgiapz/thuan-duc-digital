<?php

namespace App\Models\Concerns;

use App\Models\Organization\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTenant
{
    public static function bootHasTenant(): void
    {
        static::creating(function (self $model) {
            if (empty($model->company_id) && $companyId = static::currentCompanyId()) {
                $model->company_id = $companyId;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany(Builder $query, string $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    protected static function currentCompanyId(): ?string
    {
        return session('current_company_id') ?? config('factorymind.default_company_id');
    }
}
