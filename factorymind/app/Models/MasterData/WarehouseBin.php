<?php

namespace App\Models\MasterData;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseBin extends Model
{
    use HasUuids, HasTenant;

    protected $table = 'warehouse_bins';

    protected $fillable = [
        'company_id', 'warehouse_id', 'zone_id', 'rack_id',
        'code', 'bin_type', 'qr_code', 'max_weight_kg', 'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'max_weight_kg' => 'decimal:2',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'zone_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'rack_id');
    }

    public function generateQrCode(): string
    {
        if (! $this->qr_code) {
            $stripped = str_replace('-', '', $this->id);
            $this->qr_code = 'BIN-' . strtoupper(substr($stripped, -12));
            $this->save();
        }
        return $this->qr_code;
    }
}
