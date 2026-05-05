<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

trait HasAuditFields
{
    public static function bootHasAuditFields(): void
    {
        static::creating(function (self $model) {
            if (empty($model->created_by_id) && $userId = static::currentUserId()) {
                $model->created_by_id = $userId;
                $model->updated_by_id = $userId;
            }
        });

        static::updating(function (self $model) {
            if ($userId = static::currentUserId()) {
                $model->updated_by_id = $userId;
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    protected static function currentUserId(): ?string
    {
        return auth()->id();
    }
}
