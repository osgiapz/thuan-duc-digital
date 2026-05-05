<?php

namespace App\Models\MasterData;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model
{
    use HasUuids, HasTenant;

    protected $table = 'bom_items';

    protected $fillable = [
        'bom_id', 'company_id', 'sequence', 'material_id',
        'quantity', 'uom', 'scrap_pct', 'is_phantom',
        'operation_step', 'notes',
    ];

    protected $casts = [
        'quantity'   => 'decimal:4',
        'scrap_pct'  => 'decimal:4',
        'is_phantom' => 'boolean',
        'sequence'   => 'integer',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'material_id');
    }

    public function netQuantity(): float
    {
        return (float) $this->quantity * (1 + ($this->scrap_pct / 100));
    }
}
