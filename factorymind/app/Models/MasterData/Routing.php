<?php

namespace App\Models\MasterData;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Routing extends Model
{
    use HasUuids, HasTenant;

    protected $fillable = [
        'company_id', 'product_id', 'code', 'name', 'version',
        'effective_from', 'effective_to', 'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'is_active'      => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RoutingStep::class)->orderBy('step_number');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
