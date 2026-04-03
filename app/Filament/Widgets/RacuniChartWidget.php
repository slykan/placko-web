<?php

namespace App\Filament\Widgets;

use App\Models\Racun;
use Filament\Widgets\Widget;

class RacuniChartWidget extends Widget
{
    protected static ?int $sort = 3;

    protected static string $view = 'filament.widgets.donut-chart';

    protected int | string | array $columnSpan = 1;

    public string $heading    = 'Računi';
    public array  $segments   = [];
    public int    $total      = 0;

    public function mount(): void
    {
        $tvrtkaId = filament()->getTenant()?->id;

        $placeno   = Racun::where('tvrtka_id', $tvrtkaId)->whereNotNull('placen_at')->count();
        $neplaceno = Racun::where('tvrtka_id', $tvrtkaId)->whereNull('placen_at')->count();
        $this->total = $placeno + $neplaceno;

        $this->segments = [
            ['label' => 'Plaćeno',    'value' => $placeno,   'color' => '#22c55e'],
            ['label' => 'Neplaćeno',  'value' => $neplaceno, 'color' => '#ef4444'],
        ];
    }
}
