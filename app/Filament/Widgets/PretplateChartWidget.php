<?php

namespace App\Filament\Widgets;

use App\Models\Pretplata;
use Filament\Widgets\Widget;

class PretplateChartWidget extends Widget
{
    protected static ?int $sort = 4;

    protected static string $view = 'filament.widgets.donut-chart';

    protected int | string | array $columnSpan = 1;

    public string $heading  = 'Pretplate';
    public array  $segments = [];
    public int    $total    = 0;

    public function mount(): void
    {
        $tvrtkaId = filament()->getTenant()?->id;

        $aktivne   = Pretplata::where('tvrtka_id', $tvrtkaId)->where('status', 'aktivna')->count();
        $neaktivne = Pretplata::where('tvrtka_id', $tvrtkaId)->where('status', 'neaktivna')->count();
        $istekle   = Pretplata::where('tvrtka_id', $tvrtkaId)->where('status', 'istekla')->count();
        $this->total = $aktivne + $neaktivne + $istekle;

        $this->segments = [
            ['label' => 'Aktivne',   'value' => $aktivne,   'color' => '#22c55e'],
            ['label' => 'Neaktivne', 'value' => $neaktivne, 'color' => '#94a3b8'],
            ['label' => 'Istekle',   'value' => $istekle,   'color' => '#ef4444'],
        ];
    }
}
