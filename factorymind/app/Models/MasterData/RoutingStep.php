<?php

namespace App\Models\MasterData;

use App\Models\Concerns\HasTenant;
use App\Models\Organization\MachineCategory;
use App\Models\Organization\ProductionLine;
use App\Models\Organization\Workshop;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingStep extends Model
{
    use HasUuids, HasTenant;

    protected $table = 'routing_steps';

    protected $fillable = [
        'routing_id', 'company_id', 'step_number', 'name', 'operation_code',
        'workshop_id', 'line_id', 'machine_category_id',
        'std_time_minutes', 'setup_time_minutes', 'labor_count',
        'output_product_id', 'yield_pct', 'notes',
    ];

    protected $casts = [
        'step_number'        => 'integer',
        'labor_count'        => 'integer',
        'std_time_minutes'   => 'decimal:4',
        'setup_time_minutes' => 'decimal:4',
        'yield_pct'          => 'decimal:4',
    ];

    public function routing(): BelongsTo
    {
        return $this->belongsTo(Routing::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class, 'line_id');
    }

    public function machineCategory(): BelongsTo
    {
        return $this->belongsTo(MachineCategory::class, 'machine_category_id');
    }

    public function outputProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'output_product_id');
    }

    public function totalTimeMinutes(float $quantity = 1): float
    {
        return (float) $this->setup_time_minutes + ((float) $this->std_time_minutes * $quantity);
    }
}
