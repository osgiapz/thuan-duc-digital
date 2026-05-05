<?php

namespace App\Models\Organization;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionLine extends Model
{
    use HasUuids, HasTenant, SoftDeletes;

    protected $table = 'production_lines';

    protected $fillable = [
        'company_id', 'plant_id', 'workshop_id', 'code', 'name',
        'line_type', 'capacity_per_hour', 'capacity_uom', 'status', 'meta',
    ];

    protected $casts = [
        'meta'              => 'array',
        'capacity_per_hour' => 'decimal:4',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class, 'line_id');
    }
}
