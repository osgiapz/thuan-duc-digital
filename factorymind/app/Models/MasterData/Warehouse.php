<?php

namespace App\Models\MasterData;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Organization\Plant;

class Warehouse extends Model
{
    use HasUuids, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id', 'plant_id', 'code', 'name',
        'warehouse_type', 'is_active', 'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(WarehouseZone::class);
    }

    public function racks(): HasMany
    {
        return $this->hasMany(WarehouseRack::class);
    }

    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isWip(): bool
    {
        return $this->warehouse_type === 'wip';
    }

    public function isFinishedGoods(): bool
    {
        return $this->warehouse_type === 'finished_goods';
    }
}
