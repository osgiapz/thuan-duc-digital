<?php

namespace App\Models;

use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\Organization\Plant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorkContext extends Model
{
    use HasUuids;

    protected $table = 'user_work_contexts';

    protected $fillable = [
        'user_id', 'company_id', 'plant_id',
        'department_id', 'role_name', 'context_label', 'switched_at',
    ];

    protected $casts = [
        'switched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
