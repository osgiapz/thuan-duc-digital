<?php

namespace App\Models\Organization;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineCategory extends Model
{
    use HasUuids, HasTenant;

    protected $table = 'machine_categories';

    protected $fillable = ['company_id', 'code', 'name', 'description'];

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }
}
