<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Plan;
use App\Mail\NovaRegistracijaMail;
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
use Illuminate\Support\Facades\Mail;

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

    protected static function booted(): void
    {
        // Nova registracija (obrazac ili Google) → mail svim adminima, nakon što korisnik dobije odgovor.
        static::created(function (User $user) {
            if (auth()->user()?->isAdmin()) {
                return;
            }
            dispatch(fn () => static::javiAdminimaNovuRegistraciju($user->id))->afterResponse();
        });
    }

    public static function javiAdminimaNovuRegistraciju(int $userId): void
    {
        $korisnik = static::find($userId);
        if (! $korisnik) {
            return;
        }

        $admini = static::where('is_admin', true)->whereKeyNot($userId)->with('tvrtke')->get();
        $primatelji = $admini->isNotEmpty()
            ? $admini->map(fn (User $a) => [$a->email, $a->tvrtke->first()?->id])
            : collect([[config('mail.from.address'), null]]);

        foreach ($primatelji as [$email, $tenantId]) {
            if (! $email) {
                continue;
            }
            try {
                Mail::to($email)->send(new NovaRegistracijaMail(
                    $korisnik,
                    $tenantId ? url('/admin/'.$tenantId.'/korisnici/'.$korisnik->id.'/edit') : null,
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }
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
