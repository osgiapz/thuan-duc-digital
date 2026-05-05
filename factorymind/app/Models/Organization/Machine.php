<?php

namespace App\Models\Organization;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasUuids, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id', 'plant_id', 'workshop_id', 'line_id', 'machine_category_id',
        'code', 'name', 'serial_number', 'model', 'manufacturer', 'purchase_date',
        'theoretical_capacity', 'capacity_uom', 'status', 'meta',
    ];

    protected $casts = [
        'purchase_date'         => 'date',
        'meta'                  => 'array',
        'theoretical_capacity'  => 'decimal:4',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class, 'line_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MachineCategory::class, 'machine_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isDown(): bool
    {
        return $this->status === 'breakdown';
    }
}
