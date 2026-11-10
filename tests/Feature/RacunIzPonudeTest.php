<?php

namespace Tests\Feature;

use App\Filament\Resources\RacunResource\Pages\EditRacun;
use App\Filament\Resources\RacunResource\Pages\ListRacuni;
use App\Models\Klijent;
use App\Models\Ponuda;
use App\Models\Racun;
use App\Models\Tvrtka;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RacunIzPonudeTest extends TestCase
{
    use RefreshDatabase;

    public static function kolicine(): array
    {
        return ['cijeli broj iz baze' => ['1.000'], 'decimalna kolicina' => ['1.500'], 'manje od jedan' => ['0.250']];
    }

    private function racunIzPonude(string $kolicina): Racun
    {
        Mail::fake();
        $user = User::factory()->create();
        $tvrtka = Tvrtka::create(['naziv' => 'Test', 'oib' => '12345678903']);
        $tvrtka->users()->attach($user);
        $this->actingAs($user);
        Filament::setTenant($tvrtka);
        $klijent = Klijent::create(['tvrtka_id' => $tvrtka->id, 'naziv' => 'Kupac']);
        $ponuda = Ponuda::create([
            'tvrtka_id' => $tvrtka->id, 'klijent_id' => $klijent->id,
            'broj' => '1-1-'.now()->year, 'redni_broj' => 1, 'godina' => now()->year,
            'datum_izdavanja' => today(),
        ]);
        $ponuda->stavke()->create([
            'naziv' => 'Testna usluga', 'jedinica_mjere' => 'sat',
            'kolicina' => $kolicina, 'cijena' => 100, 'rabat_posto' => 10,
            'pdv_stopa' => 25, 'ukupno' => (float) $kolicina * 112.5, 'redni_broj' => 1,
        ]);
        Livewire::test(ListRacuni::class)
            ->callAction('iz_ponude', data: ['ponuda_id' => $ponuda->id])
            ->assertHasNoActionErrors();

        return Racun::sole();
    }

    #[DataProvider('kolicine')]
    public function test_racun_iz_ponude_moze_se_spremiti_bez_promjene_kolicine(string $kolicina): void
    {
        $racun = $this->racunIzPonude($kolicina);
        Livewire::test(EditRacun::class, ['record' => $racun->id])
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame($kolicina, $racun->stavke()->sole()->kolicina);
        $this->assertEquals(round((float) $kolicina * 112.5, 2), $racun->fresh()->ukupno);
    }

    public function test_promjena_kolicine_osvjezava_stavku_sazetak_i_spremljeni_racun(): void
    {
        $racun = $this->racunIzPonude('1.000');
        $page = Livewire::test(EditRacun::class, ['record' => $racun->id]);
        $key = array_key_first($page->get('data.stavke'));
        $page->set("data.stavke.$key.kolicina", '1.5')
            ->assertSet("data.stavke.$key.ukupno", 168.75)
            ->assertSet('data.ukupno_osnovica', 150)
            ->assertSet('data.ukupno_rabat', 15)
            ->assertSet('data.ukupno_pdv', 33.75)
            ->assertSet('data.ukupno', 168.75)
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('1.500', $racun->stavke()->sole()->kolicina);
        $this->assertEquals(168.75, $racun->fresh()->ukupno);
    }

    public function test_kolicina_nula_i_tekst_i_dalje_se_odbijaju(): void
    {
        $racun = $this->racunIzPonude('1.000');
        $page = Livewire::test(EditRacun::class, ['record' => $racun->id]);
        $key = array_key_first($page->get('data.stavke'));
        $page->set("data.stavke.$key.kolicina", '0')->call('save')
            ->assertHasFormErrors(["stavke.$key.kolicina" => 'min']);
        $page->set("data.stavke.$key.kolicina", 'abc')->call('save')
            ->assertHasFormErrors(["stavke.$key.kolicina" => 'numeric']);
        $this->assertSame('1.000', $racun->stavke()->sole()->kolicina);
    }
}
