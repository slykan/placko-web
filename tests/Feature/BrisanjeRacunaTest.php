<?php

namespace Tests\Feature;

use App\Filament\Pages\Postavke;
use App\Models\Klijent;
use App\Models\Ponuda;
use App\Models\Racun;
use App\Models\Tvrtka;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BrisanjeRacunaTest extends TestCase
{
    use RefreshDatabase;

    private function tvrtkaSPodacima(User $user, string $naziv): Tvrtka
    {
        $tvrtka = Tvrtka::create(['naziv' => $naziv, 'oib' => '12345678903']);
        $tvrtka->users()->attach($user);
        $klijent = Klijent::create(['tvrtka_id' => $tvrtka->id, 'naziv' => 'Kupac']);
        $ponuda = Ponuda::create([
            'tvrtka_id' => $tvrtka->id, 'klijent_id' => $klijent->id,
            'broj' => '1-1-'.now()->year, 'redni_broj' => 1, 'godina' => now()->year,
            'datum_izdavanja' => today(),
        ]);
        $ponuda->stavke()->create([
            'naziv' => 'Usluga', 'jedinica_mjere' => 'sat', 'kolicina' => 1, 'cijena' => 100,
            'rabat_posto' => 0, 'pdv_stopa' => 25, 'ukupno' => 125, 'redni_broj' => 1,
        ]);
        Racun::create([
            'tvrtka_id' => $tvrtka->id, 'klijent_id' => $klijent->id,
            'broj' => '1-1-1', 'redni_broj' => 1, 'godina' => now()->year,
            'datum_izdavanja' => today(),
        ]);

        return $tvrtka;
    }

    public function test_brise_racun_i_vlastite_tvrtke_a_dijeljenu_ostavlja(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['email' => 'brisi@example.com']);
        $drugi = User::factory()->create();

        $vlastita = $this->tvrtkaSPodacima($user, 'Moj obrt');
        $dijeljena = $this->tvrtkaSPodacima($user, 'Zajednička');
        $dijeljena->users()->attach($drugi);
        Storage::disk('local')->put("fina/{$vlastita->id}/cert.p12", 'x');
        Storage::disk('local')->put("racuni/{$vlastita->id}/2026/Racun_1.pdf", 'x');

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($vlastita);

        Livewire::test(Postavke::class)
            ->callAction('obrisiRacun', data: ['potvrda' => 'krivo@example.com'])
            ->assertHasActionErrors(['potvrda']);
        $this->assertModelExists($user);

        Livewire::test(Postavke::class)
            ->callAction('obrisiRacun', data: ['potvrda' => ' BRISI@example.com '])
            ->assertHasNoActionErrors()
            ->assertRedirect('/brisanje-racuna?obrisan=1');

        $this->assertModelMissing($user);
        $this->assertModelMissing($vlastita);
        $this->assertSame(0, DB::table('racuni')->where('tvrtka_id', $vlastita->id)->count());
        $this->assertSame(0, DB::table('klijenti')->where('tvrtka_id', $vlastita->id)->count());
        $this->assertSame(0, DB::table('ponuda_stavke')->count() - 1, 'ostaje samo stavka dijeljene tvrtke');
        Storage::disk('local')->assertMissing("fina/{$vlastita->id}/cert.p12");
        Storage::disk('local')->assertMissing("racuni/{$vlastita->id}/2026/Racun_1.pdf");

        $this->assertModelExists($dijeljena);
        $this->assertSame(1, DB::table('racuni')->where('tvrtka_id', $dijeljena->id)->count());
        $this->assertTrue($dijeljena->users()->whereKey($drugi->id)->exists());
        $this->assertGuest();
    }

    public function test_javna_stranica_za_brisanje_postoji(): void
    {
        $this->get('/brisanje-racuna')->assertOk()->assertSee('Opasna zona');
    }
}
