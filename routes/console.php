<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pretplate:podsjetnici')->dailyAt('08:00');

Artisan::command('push:novosti', function () {
    $poslano = \App\Support\PushNovosti::posalji();
    foreach ($poslano as $slug => $n) {
        $this->info("{$slug}: {$n} uređaja");
    }
    if (! $poslano) {
        $this->info('Nema novih objava za slanje.');
    }
})->purpose('Push obavijest o novoj objavi iz zvona (kategorija novosti), svakoj jednom');

Schedule::command('push:novosti')->hourlyAt(5)->between('8:00', '20:00');
