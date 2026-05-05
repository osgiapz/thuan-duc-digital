<?php

namespace App\Models\Organization;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plant extends Model
{
    use HasUuids, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id', 'code', 'name', 'plant_type',
        'manager_user_id', 'address', 'coordinates', 'status', 'meta',
    ];

    protected $casts = [
        'address'     => 'array',
        'coordinates' => 'array',
        'meta'        => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class);
    }

    public function productionLines(): HasMany
    {
        return $this->hasMany(ProductionLine::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
