<?php

namespace App\Enums;

/**
 * Paketi plačko.app (cijene su u config/cjenik.php).
 *  - Starter: sve mogućnosti za jednu tvrtku/obrt, bez podrške
 *  - Pro: + više tvrtki na jednom računu, podrška e-mailom
 *  - Business: + prioritetna podrška (SLA) i zahtjevi za nadogradnju
 */
enum Plan: string
{
    case Starter = 'starter';
    case Pro = 'pro';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Starter => 'Starter',
            self::Pro => 'Pro',
            self::Business => 'Business',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Starter => 'gray',
            self::Pro => 'primary',
            self::Business => 'warning',
        };
    }

    public function viseTvrtki(): bool
    {
        return $this !== self::Starter;
    }

    public function podrska(): bool
    {
        return $this !== self::Starter;
    }

    public function prioritetnaPodrska(): bool
    {
        return $this === self::Business;
    }

    /** @return array<string, string> za Select polja */
    public static function opcije(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $p) => [$p->value => $p->label()])->all();
    }
}
