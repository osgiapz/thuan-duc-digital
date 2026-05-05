<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseRack extends Model
{
    use HasUuids;

    protected $table = 'warehouse_racks';

    protected $fillable = ['warehouse_id', 'zone_id', 'code', 'name'];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'zone_id');
    }

    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class, 'rack_id');
    }
}
