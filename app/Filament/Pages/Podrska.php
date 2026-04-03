<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Podrska extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Pomoć i podrška';
    protected static ?string $title = 'Pomoć i podrška';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.podrska';
}
