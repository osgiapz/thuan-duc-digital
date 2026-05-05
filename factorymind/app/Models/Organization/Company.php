<?php

namespace App\Models\Organization;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasUuids;

    protected $fillable = [
        'parent_id', 'code', 'name', 'legal_name', 'tax_code',
        'company_type', 'currency_code', 'fiscal_year_start',
        'status', 'address', 'contact', 'meta',
    ];

    protected $casts = [
        'address'            => 'array',
        'contact'            => 'array',
        'meta'               => 'array',
        'fiscal_year_start'  => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_id');
    }

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeGroups($query)
    {
        return $query->where('company_type', 'group');
    }

    public function isGroup(): bool
    {
        return $this->company_type === 'group';
    }
}
