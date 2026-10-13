<?php

namespace Tests\Feature;

use App\Models\Racun;
use App\Models\Tvrtka;
use App\Models\TvrtkaPostavke;
use App\Services\FiskalizacijaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FiskalizacijaObvezaTest extends TestCase
{
    use RefreshDatabase;

    private function racun(string $nacin, bool $gradanin, bool $aktivna = true): Racun
    {
        $t = Tvrtka::query()->insertGetId(['naziv' => 'Test obrt', 'oib' => '12345678903', 'created_at' => now(), 'updated_at' => now()]);
        TvrtkaPostavke::create(['tvrtka_id' => $t, 'fiskalizacija_aktivna' => $aktivna]);
        $k = DB::table('klijenti')->insertGetId(['tvrtka_id' => $t, 'naziv' => 'Kupac', 'fizicka_osoba' => $gradanin, 'created_at' => now(), 'updated_at' => now()]);
        $id = DB::table('racuni')->insertGetId([
            'tvrtka_id' => $t, 'klijent_id' => $k, 'redni_broj' => 1, 'godina' => now()->year,
            'broj' => '1-1-'.now()->year, 'datum_izdavanja' => now()->toDateString(), 'nacin_placanja' => $nacin,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return Racun::findOrFail($id);
    }

    public function test_gotovina_i_kartica_uvijek(): void
    {
        $this->assertTrue(FiskalizacijaService::trebaMFiskalizirati($this->racun('gotovina', false)));
        $this->assertTrue(FiskalizacijaService::trebaMFiskalizirati($this->racun('kartica', false)));
    }

    public function test_transakcijski_gradaninu_se_fiskalizira(): void
    {
        $this->assertTrue(FiskalizacijaService::trebaMFiskalizirati($this->racun('transakcijski', true)));
        $this->assertTrue(FiskalizacijaService::trebaMFiskalizirati($this->racun('virman', true)));
    }

    public function test_transakcijski_tvrtki_se_ne_fiskalizira(): void
    {
        $this->assertFalse(FiskalizacijaService::trebaMFiskalizirati($this->racun('transakcijski', false)));
    }

    public function test_bez_ukljucene_fiskalizacije_nista(): void
    {
        $this->assertFalse(FiskalizacijaService::trebaMFiskalizirati($this->racun('gotovina', true, false)));
    }

    public function test_oznaka_nacina_placanja(): void
    {
        $this->assertSame('T', FiskalizacijaService::nacinPlacanja('transakcijski'));
        $this->assertSame('T', FiskalizacijaService::nacinPlacanja('virman'));
        $this->assertSame('G', FiskalizacijaService::nacinPlacanja('gotovina'));
    }
}
