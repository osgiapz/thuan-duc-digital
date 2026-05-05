<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasUuids, SoftDeletes, Notifiable, HasRoles;

    protected $fillable = [
        'email', 'phone', 'password', 'display_name',
        'avatar_url', 'locale', 'timezone', 'status',
        'last_login_at', 'last_login_ip', 'meta',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'meta'              => 'array',
        ];
    }

    public function workContext(): HasOne
    {
        return $this->hasOne(UserWorkContext::class);
    }

    public function currentCompanyId(): ?string
    {
        return $this->workContext?->company_id;
    }

    public function currentPlantId(): ?string
    {
        return $this->workContext?->plant_id;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active';
    }
}
