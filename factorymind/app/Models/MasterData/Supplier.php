<?php

namespace App\Models\MasterData;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasUuids, HasTenant, HasAuditFields, SoftDeletes;

    protected $fillable = [
        'company_id', 'code', 'name', 'legal_name', 'tax_code',
        'supplier_type', 'lead_time_days', 'payment_days',
        'status', 'address', 'meta',
    ];

    protected $casts = [
        'address' => 'array',
        'meta'    => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isSubcontractor(): bool
    {
        return $this->supplier_type === 'subcontract';
    }
}
