<?php

namespace Tests\Feature;

use App\Filament\Resources\LokoVoznjaResource;
use App\Filament\Resources\PutniNalogResource;
use App\Filament\Resources\PutniNalogResource\Pages\CreatePutniNalog;
use App\Models\Klijent;
use App\Models\LokoVoznja;
use App\Models\PutniNalog;
use App\Models\RadniNalog;
use App\Models\RadniNalogRad;
use App\Models\Tvrtka;
use App\Models\User;
use App\Models\Zaposlenik;
use App\Support\PutniObracun;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PutniNaloziTest extends TestCase
{
    use RefreshDatabase;

    private Tvrtka $tvrtka;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->forceFill(['plan' => 'pro'])->save();
        $this->tvrtka = Tvrtka::create(['naziv' => 'Servis d.o.o.', 'oib' => '12345678903', 'adresa' => 'Ilirska 27', 'mjesto' => 'Osijek']);
        $this->tvrtka->users()->attach($this->user);
    }

    private function prijavi(): void
    {
        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($this->tvrtka);
    }

    private function nalog(array $podaci = []): PutniNalog
    {
        $n = PutniNalog::create(array_merge(PutniNalog::generiraBroj($this->tvrtka->id), [
            'tvrtka_id' => $this->tvrtka->id, 'putnik' => 'Ivan Ivić', 'datum_naloga' => '2026-11-10',
            'odrediste' => 'Zagreb', 'svrha' => 'Sastanak', 'prijevoz' => 'privatni_auto', 'registracija' => 'OS-123-AB',
        ], $podaci));
        $n->izracunaj();

        return $n->fresh('troskovi');
    }

    public function test_broj_dnevnica_po_satima(): void
    {
        $this->assertSame([0, 0], PutniObracun::brojDnevnica(8));
        $this->assertSame([0, 1], PutniObracun::brojDnevnica(8.5));
        $this->assertSame([0, 1], PutniObracun::brojDnevnica(12));
        $this->assertSame([1, 0], PutniObracun::brojDnevnica(12.5));
        $this->assertSame([1, 1], PutniObracun::brojDnevnica(34));
        $this->assertSame([2, 0], PutniObracun::brojDnevnica(37));
    }

    public function test_tuzemni_obracun_dnevnice_km_troskovi_predujam_i_obroci(): void
    {
        $n = $this->nalog([
            'polazak_at' => '2026-11-10 06:00', 'povratak_at' => '2026-11-11 16:00', // 34 h = 1,5 dnevnica
            'km_pocetno' => 10000, 'km_zavrsno' => 10600, 'predujam' => 20,
        ]);
        $n->troskovi()->create(['vrsta' => 'cestarina', 'iznos' => 12.40]);
        $n->izracunaj();

        $this->assertSame(45.0, (float) $n->ukupno_dnevnice);          // 1,5 × 30 €
        $this->assertSame(300.0, (float) $n->ukupno_km);               // 600 km × 0,50 €
        $this->assertSame(12.4, (float) $n->ukupno_troskovi);
        $this->assertSame(337.4, (float) $n->za_isplatu);              // 45 + 300 + 12,40 − 20

        $n->update(['obroci' => 1]);
        $n->izracunaj();
        $this->assertSame(31.5, (float) $n->ukupno_dnevnice, 'jedan osiguran obrok = −30 %');
    }

    public function test_sluzbeni_auto_nema_naknade_po_km(): void
    {
        $n = $this->nalog(['prijevoz' => 'sluzbeni_auto', 'polazak_at' => '2026-11-10 07:00', 'povratak_at' => '2026-11-10 20:00', 'km' => 300]);
        $this->assertSame(0.0, (float) $n->ukupno_km);
        $this->assertSame(30.0, (float) $n->ukupno_dnevnice);
    }

    public function test_inozemni_eur_i_usd_s_tecajem_hnb(): void
    {
        // Njemačka 90 € (NN 16/2024): 13 h vani = 1 dnevnica, plus 9 h u Hrvatskoj = pola tuzemne
        $n = $this->nalog([
            'vrsta' => 'inozemni', 'drzava' => 'Njemačka', 'odrediste' => 'München',
            'polazak_at' => '2026-11-10 03:00', 'granica_odlazak_at' => '2026-11-10 07:30',
            'granica_povratak_at' => '2026-11-10 20:30', 'povratak_at' => '2026-11-11 01:00',
        ]);
        $this->assertSame(105.0, (float) $n->ukupno_dnevnice);

        Http::fake(['api.hnb.hr/*' => Http::response([['valuta' => 'USD', 'srednji_tecaj' => '1,1600']])]);
        $usa = $this->nalog([
            'vrsta' => 'inozemni', 'drzava' => 'SAD', 'odrediste' => 'New York', 'prijevoz' => 'zrakoplov', 'registracija' => null,
            'polazak_at' => '2026-11-10 06:00', 'povratak_at' => '2026-11-12 06:00', // 48 h = 2 × 100 USD
        ]);
        $this->assertSame(1.16, (float) $usa->tecaj);
        $this->assertSame(round(200 / 1.16, 2), (float) $usa->ukupno_dnevnice);
    }

    public function test_kreiranje_u_adminu_trazi_registraciju_za_auto_i_daje_pdf(): void
    {
        $this->prijavi();

        Livewire::test(CreatePutniNalog::class)
            ->fillForm([
                'putnik' => 'Ana Anić', 'odrediste' => 'Split', 'svrha' => 'Montaža', 'vrsta' => 'tuzemni',
                'prijevoz' => 'privatni_auto', 'registracija' => '', 'polazak_at' => '2026-11-10 06:00', 'povratak_at' => '2026-11-10 20:00',
            ])->call('create')
            ->assertHasFormErrors(['registracija' => 'required']);

        Livewire::test(CreatePutniNalog::class)
            ->fillForm([
                'putnik' => 'Ana Anić', 'odrediste' => 'Split', 'svrha' => 'Montaža', 'vrsta' => 'tuzemni',
                'prijevoz' => 'privatni_auto', 'registracija' => 'st 555 aa', 'km' => 820,
                'polazak_at' => '2026-11-10 06:00', 'povratak_at' => '2026-11-10 20:00',
            ])->call('create')
            ->assertHasNoFormErrors();

        $n = PutniNalog::firstOrFail();
        $this->assertSame('PN-1-'.now()->year, $n->broj);
        $this->assertSame('ST 555 AA', $n->registracija);
        $this->assertSame(440.0, (float) $n->za_isplatu); // 30 € + 820 km × 0,50 €

        $pdf = PutniNalogResource::generirajPdf($n);
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->get(PutniNalogResource::getUrl('index'))->assertOk();
        $this->get(PutniNalogResource::getUrl('edit', ['record' => $n]))->assertOk()->assertSee('Za isplatu');
    }

    public function test_starter_nema_putne_naloge(): void
    {
        $this->user->forceFill(['plan' => 'starter'])->save();
        $this->prijavi();
        $this->assertFalse(PutniNalogResource::can('viewAny'));
        $this->assertFalse(LokoVoznjaResource::can('viewAny'));
    }

    public function test_zaposlenik_upisuje_podatke_i_racun_preko_osobnog_linka(): void
    {
        Storage::fake('local');
        $z = Zaposlenik::create(['tvrtka_id' => $this->tvrtka->id, 'ime' => 'Ivan', 'prezime' => 'Ivić', 'registracija' => 'OS-123-AB']);
        $z->noviPortalToken();
        $n = $this->nalog(['zaposlenik_id' => $z->id, 'polazak_at' => '2026-11-10 06:00']);

        $this->get(route('moji-nalozi.index', $z->portal_token))->assertOk()->assertSee($n->broj);
        $this->get(route('moji-nalozi.putni', [$z->portal_token, $n->id]))->assertOk()->assertSee('OS-123-AB');

        $this->post(route('moji-nalozi.putni.spremi', [$z->portal_token, $n->id]), [
            'polazak' => '2026-11-10T06:00', 'povratak' => '2026-11-10T19:00',
            'km_pocetno' => 5000, 'km_zavrsno' => 5200, 'izvjesce' => 'Obavljen servis.',
            'trosak_vrsta' => 'parking', 'trosak_iznos' => '3.50', 'trosak_slika' => UploadedFile::fake()->image('racun.jpg'),
        ])->assertRedirect();

        $n->refresh();
        $this->assertSame(133.5, (float) $n->za_isplatu); // 30 + 100 + 3,50
        $this->assertSame('Obavljen servis.', $n->izvjesce);
        Storage::disk('local')->assertExists($n->troskovi->first()->privitak);

        $tudi = Zaposlenik::create(['tvrtka_id' => $this->tvrtka->id, 'ime' => 'Drugi', 'prezime' => 'Radnik']);
        $tudi->noviPortalToken();
        $this->get(route('moji-nalozi.putni', [$tudi->portal_token, $n->id]))->assertNotFound();

        $n->update(['status' => 'obracunat']);
        $this->post(route('moji-nalozi.putni.spremi', [$z->portal_token, $n->id]), ['izvjesce' => 'x'])->assertForbidden();
    }

    public function test_loko_voznja_uvoz_iz_radnih_naloga_i_pdf(): void
    {
        $this->prijavi();
        $z = Zaposlenik::create(['tvrtka_id' => $this->tvrtka->id, 'ime' => 'Ivan', 'prezime' => 'Ivić', 'registracija' => 'OS-123-AB']);
        $k = Klijent::create(['tvrtka_id' => $this->tvrtka->id, 'naziv' => 'Kupac', 'mjesto' => 'Josipovac']);
        $rn = RadniNalog::create([...RadniNalog::generiraBroj($this->tvrtka->id), 'tvrtka_id' => $this->tvrtka->id, 'klijent_id' => $k->id,
            'datum_otvaranja' => today(), 'lokacija' => 'Josipovac, Vukovarska 1', 'opis_posla' => 'Servis']);
        $mjesec = now()->format('Y-m');
        RadniNalogRad::create(['radni_nalog_id' => $rn->id, 'zaposlenik_id' => $z->id, 'datum' => now()->startOfMonth()->addDays(2), 'vrijeme_dolaska' => '08:30', 'sati' => 2]);

        $this->assertSame(1, LokoVoznjaResource::uveziIzRadnihNaloga($this->tvrtka->id, $z->id, $mjesec, 'Osijek', null));
        $this->assertSame(0, LokoVoznjaResource::uveziIzRadnihNaloga($this->tvrtka->id, $z->id, $mjesec, 'Osijek', null), 'drugi uvoz ne duplicira');

        $v = LokoVoznja::firstOrFail();
        $this->assertSame('Osijek – Josipovac, Vukovarska 1 – Osijek', $v->relacija);
        $this->assertSame('OS-123-AB', $v->registracija);
        $this->assertStringContainsString($rn->broj, $v->svrha);

        $v->update(['km' => 24]);
        $this->assertSame(12.0, $v->iznos());
        $this->assertStringStartsWith('%PDF', LokoVoznjaResource::generirajPdf($this->tvrtka->id, $mjesec, null, 'Ivan Ivić'));
        $this->get(LokoVoznjaResource::getUrl('index'))->assertOk();
    }

    public function test_uputa_u_novostima_i_naslovnica_bez_uskoro(): void
    {
        $this->get('/novosti/putni-nalozi-i-loko-voznja')->assertOk()->assertSee('Loko vožnja iz radnih naloga');
        $this->assertStringNotContainsString('feature-card uskoro', $this->get('/')->getContent());
    }

    public function test_tablica_inozemnih_dnevnica_ima_sve_drzave_iz_odluke(): void
    {
        $t = config('putni.inozemne');
        $this->assertCount(106, $t);
        $this->assertSame([90.0, 'EUR'], $t['Njemačka']);
        $this->assertSame([80.0, 'EUR'], $t['Slovenija']);
        $this->assertSame([50.0, 'EUR'], $t['Bosna i Hercegovina']);
        $this->assertSame([50.0, 'USD'], $t['Sve druge države']);
    }
}
