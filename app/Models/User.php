<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Plan;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'plan_do' => 'date',
            'is_admin' => 'boolean',
        ];
    }

    /** Paket koji trenutno vrijedi — nakon isteka plan_do korisnik je Starter. */
    public function paket(): Plan
    {
        $plan = Plan::tryFrom((string) $this->plan) ?? Plan::Starter;

        if ($plan !== Plan::Starter && $this->plan_do && $this->plan_do->endOfDay()->isPast()) {
            return Plan::Starter;
        }

        return $plan;
    }

    public function mozeDodatiTvrtku(): bool
    {
        return $this->paket()->viseTvrtki() || ! $this->tvrtke()->exists();
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function tvrtke(): BelongsToMany
    {
        return $this->belongsToMany(Tvrtka::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->tvrtke;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tvrtke()->whereKey($tenant)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
