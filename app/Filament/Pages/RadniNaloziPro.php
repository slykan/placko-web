<?php

namespace App\Filament\Pages;

use App\Filament\Resources\RadniNalogResource;
use Filament\Pages\Page;

/** Starter: umjesto Radnih naloga i Zaposlenika vidi ovu stranicu s pozivom na nadogradnju. */
class RadniNaloziPro extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Radni nalozi';
    protected static ?string $title = 'Radni nalozi i zaposlenici';
    protected static ?string $slug = 'radni-nalozi-pro';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.radni-nalozi-pro';

    public static function getNavigationBadge(): ?string
    {
        return 'Pro';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    public static function canAccess(): bool
    {
        return ! RadniNalogResource::dostupno();
    }

    public function mount(): void
    {
        if (RadniNalogResource::dostupno()) {
            $this->redirect(RadniNalogResource::getUrl('index'));
        }
    }
}
