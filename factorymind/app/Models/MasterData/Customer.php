<?php

namespace App\Models\MasterData;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuids, HasTenant, HasAuditFields, SoftDeletes;

    protected $fillable = [
        'company_id', 'code', 'name', 'legal_name', 'tax_code',
        'customer_type', 'credit_limit', 'credit_limit_currency',
        'payment_days', 'status', 'billing_address', 'shipping_address', 'meta',
    ];

    protected $casts = [
        'credit_limit'     => 'decimal:4',
        'billing_address'  => 'array',
        'shipping_address' => 'array',
        'meta'             => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isExport(): bool
    {
        return $this->customer_type === 'export';
    }
}
