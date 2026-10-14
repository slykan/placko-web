<?php

namespace Tests\Feature;

use App\Enums\Plan;
use App\Filament\Pages\RegisterTvrtka;
use App\Filament\Resources\KorisnikResource;
use App\Filament\Resources\KorisnikResource\Pages\EditKorisnik;
use App\Models\Tvrtka;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaketiTest extends TestCase
{
    use RefreshDatabase;

    private function korisnikSTvrtkom(array $attrs = []): array
    {
        $u = User::factory()->create();
        $u->forceFill($attrs)->save();
        $t = Tvrtka::query()->create(['naziv' => 'Obrt '.$u->id, 'oib' => '12345678903']);
        $t->users()->attach($u);

        return [$u->fresh(), $t];
    }

    public function test_novi_korisnik_je_starter(): void
    {
        $u = User::factory()->create();
        $this->assertSame(Plan::Starter, $u->fresh()->paket());
        $this->assertFalse($u->fresh()->isAdmin());
    }

    public function test_starter_ne_moze_dodati_drugu_tvrtku_pro_moze(): void
    {
        [$starter] = $this->korisnikSTvrtkom();
        $this->actingAs($starter);
        $this->assertFalse(RegisterTvrtka::canView());

        [$pro] = $this->korisnikSTvrtkom(['plan' => 'pro']);
        $this->actingAs($pro);
        $this->assertTrue(RegisterTvrtka::canView());

        $bez = User::factory()->create();
        $this->actingAs($bez);
        $this->assertTrue(RegisterTvrtka::canView(), 'prva tvrtka je uvijek dopuštena');
    }

    public function test_istekao_paket_postaje_starter(): void
    {
        [$u] = $this->korisnikSTvrtkom(['plan' => 'business', 'plan_do' => now()->subDay()->toDateString()]);
        $this->assertSame(Plan::Starter, $u->paket());

        $u->forceFill(['plan_do' => now()->toDateString()])->save();
        $this->assertSame(Plan::Business, $u->fresh()->paket(), 'vrijedi do kraja tog dana');
    }

    public function test_korisnike_vidi_samo_admin(): void
    {
        [$admin, $t] = $this->korisnikSTvrtkom(['is_admin' => true]);
        [$obican, $t2] = $this->korisnikSTvrtkom();

        $this->actingAs($obican);
        $this->assertFalse(KorisnikResource::canViewAny());
        $this->get('/admin/'.$t2->id.'/korisnici')->assertForbidden();

        $this->actingAs($admin);
        $this->assertTrue(KorisnikResource::canViewAny());
        $this->get('/admin/'.$t->id.'/korisnici')->assertOk()->assertSee($obican->email);
    }

    public function test_admin_mijenja_paket_ali_ne_moze_sebi_ukinuti_admina(): void
    {
        [$admin, $t] = $this->korisnikSTvrtkom(['is_admin' => true]);
        [$obican] = $this->korisnikSTvrtkom();

        $this->actingAs($admin);
        Filament::setTenant($t);

        Livewire::test(EditKorisnik::class, ['record' => $obican->getKey()])
            ->fillForm(['plan' => 'pro', 'plan_do' => '2027-12-31'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(Plan::Pro, $obican->fresh()->paket());
        $this->assertSame('2027-12-31', $obican->fresh()->plan_do->toDateString());

        Livewire::test(EditKorisnik::class, ['record' => $admin->getKey()])
            ->fillForm(['is_admin' => false])
            ->call('save');
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_plan_se_ne_moze_masovno_dodijeliti(): void
    {
        $u = User::factory()->create();
        $u->fill(['plan' => 'business', 'is_admin' => true])->save();

        $this->assertSame(Plan::Starter, $u->fresh()->paket());
        $this->assertFalse($u->fresh()->isAdmin());
    }
}
