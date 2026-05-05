<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseZone extends Model
{
    use HasUuids;

    protected $table = 'warehouse_zones';

    protected $fillable = ['warehouse_id', 'code', 'name', 'zone_type'];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function racks(): HasMany
    {
        return $this->hasMany(WarehouseRack::class, 'zone_id');
    }

    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class, 'zone_id');
    }
}
