<?php

namespace Tests\Feature;

use App\Models\Racun;
use App\Models\Tvrtka;
use App\Models\TvrtkaPostavke;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RacunNumeracijaTest extends TestCase
{
    use RefreshDatabase;

    private function tvrtka(): int
    {
        return Tvrtka::query()->insertGetId(['naziv' => 'Test d.o.o.', 'oib' => '12345678903', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function racun(int $tvrtkaId, int $redni, int $godina): void
    {
        $klijentId = DB::table('klijenti')->insertGetId(['tvrtka_id' => $tvrtkaId, 'naziv' => 'Kupac', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('racuni')->insert([
            'tvrtka_id' => $tvrtkaId, 'klijent_id' => $klijentId, 'redni_broj' => $redni, 'godina' => $godina,
            'broj' => "{$redni}-1-{$godina}", 'datum_izdavanja' => now()->toDateString(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_bez_postavke_krece_od_1(): void
    {
        $t = $this->tvrtka();
        $this->assertSame(1, Racun::generiraBroj($t)['redni_broj']);
    }

    public function test_pocetni_broj_za_tekucu_godinu(): void
    {
        $t = $this->tvrtka();
        TvrtkaPostavke::create(['tvrtka_id' => $t, 'racun_pocetni_broj' => 58, 'racun_pocetni_godina' => now()->year]);

        $this->assertSame('58-1-' . now()->year, Racun::generiraBroj($t)['broj']);

        $this->racun($t, 58, now()->year);
        $this->assertSame(59, Racun::generiraBroj($t)['redni_broj']);
    }

    public function test_pocetni_broj_ne_vrijedi_za_drugu_godinu(): void
    {
        $t = $this->tvrtka();
        TvrtkaPostavke::create(['tvrtka_id' => $t, 'racun_pocetni_broj' => 58, 'racun_pocetni_godina' => now()->year - 1]);

        $this->assertSame(1, Racun::generiraBroj($t)['redni_broj']);
    }

    public function test_nize_od_postojecih_ne_stvara_duplikat(): void
    {
        $t = $this->tvrtka();
        $this->racun($t, 10, now()->year);
        TvrtkaPostavke::create(['tvrtka_id' => $t, 'racun_pocetni_broj' => 5, 'racun_pocetni_godina' => now()->year]);

        $this->assertSame(11, Racun::generiraBroj($t)['redni_broj']);
    }
}
