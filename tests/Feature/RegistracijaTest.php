<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Register;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegistracijaTest extends TestCase
{
    use RefreshDatabase;

    public function test_nova_registracija_vodi_na_stranicu_koja_postoji(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $odgovor = Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Test Korisnik',
                'email' => 'novi@example.com',
                'password' => 'Lozinka123!',
                'passwordConfirmation' => 'Lozinka123!',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'novi@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        $kamo = $odgovor->effects['redirect'] ?? null;
        $this->assertNotNull($kamo, 'Registracija nije preusmjerila nikamo');

        $this->actingAs($user)->get($kamo)->assertSuccessful();
    }
}
