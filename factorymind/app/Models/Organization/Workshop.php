<?php

namespace App\Models\Organization;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workshop extends Model
{
    use HasUuids, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id', 'plant_id', 'department_id',
        'code', 'name', 'supervisor_id', 'status', 'meta',
    ];

    protected $casts = ['meta' => 'array'];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ProductionLine::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }
}
