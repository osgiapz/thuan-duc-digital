<?php

namespace App\Models\MasterData;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids, HasTenant, HasAuditFields, SoftDeletes;

    protected $fillable = [
        'company_id', 'category_id', 'code', 'name', 'description',
        'product_type', 'base_uom', 'weight_kg', 'dimensions',
        'standard_cost', 'list_price', 'currency_code', 'lead_time_days',
        'min_order_qty', 'reorder_point', 'safety_stock',
        'is_active', 'attributes', 'meta',
    ];

    protected $casts = [
        'dimensions'    => 'array',
        'attributes'    => 'array',
        'meta'          => 'array',
        'is_active'     => 'boolean',
        'standard_cost' => 'decimal:4',
        'list_price'    => 'decimal:4',
        'weight_kg'     => 'decimal:4',
        'min_order_qty' => 'decimal:4',
        'reorder_point' => 'decimal:4',
        'safety_stock'  => 'decimal:4',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function boms(): HasMany
    {
        return $this->hasMany(Bom::class);
    }

    public function activeBom(): HasMany
    {
        return $this->hasMany(Bom::class)->where('is_active', true)->latest('effective_from');
    }

    public function routings(): HasMany
    {
        return $this->hasMany(Routing::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFinishedGoods($query)
    {
        return $query->where('product_type', 'finished_good');
    }

    public function scopeRawMaterials($query)
    {
        return $query->where('product_type', 'raw_material');
    }

    public function isFinishedGood(): bool
    {
        return $this->product_type === 'finished_good';
    }

    public function isRawMaterial(): bool
    {
        return $this->product_type === 'raw_material';
    }
}
