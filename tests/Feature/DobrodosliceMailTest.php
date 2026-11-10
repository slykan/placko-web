<?php

namespace Tests\Feature;

use App\Mail\DobrodosliceMail;
use App\Mail\NovaRegistracijaMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DobrodosliceMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_novi_korisnik_dobije_dobrodoslicu(): void
    {
        Mail::fake();
        $user = User::factory()->create(['name' => 'Ivana Horvat', 'email' => 'ivana@example.com']);
        $this->app->terminate();

        Mail::assertSent(DobrodosliceMail::class, fn ($m) => $m->hasTo('ivana@example.com'));
        Mail::assertSent(NovaRegistracijaMail::class);

        $html = (new DobrodosliceMail($user))->render();
        $this->assertStringContainsString('Hej Ivana, dobrodošlica!', $html);
        $this->assertStringContainsString(url('/admin'), $html);
    }

    public function test_korisnika_kojeg_doda_admin_ne_pozdravlja(): void
    {
        Mail::fake();
        $this->actingAs(User::withoutEvents(fn () => User::factory()->create(['is_admin' => true])));

        User::factory()->create();
        $this->app->terminate();
        Mail::assertNotSent(DobrodosliceMail::class);
    }
}
