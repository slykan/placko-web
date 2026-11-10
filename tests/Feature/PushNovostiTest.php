<?php

namespace Tests\Feature;

use App\Filament\Pages\Postavke;
use App\Livewire\NovostiZvono;
use App\Models\MobilePushDevice;
use App\Models\PushPretplata;
use App\Models\Tvrtka;
use App\Models\User;
use App\Support\BrisanjeRacuna;
use App\Support\PushNovosti;
use App\Support\WebPush;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PushNovostiTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = 'https://fcm.googleapis.com/fcm/send/test-uredjaj';

    /** Pravi P-256 ključ preglednika, da šifriranje poruke stvarno prođe. */
    private function pretplata(User $user, string $endpoint = self::ENDPOINT): PushPretplata
    {
        $par = (new \ReflectionMethod(WebPush::class, 'noviPar'))->invoke(null);

        return PushPretplata::create([
            'user_id' => $user->id, 'endpoint' => $endpoint, 'endpoint_hash' => hash('sha256', $endpoint),
            'p256dh' => WebPush::b64($par[1]), 'auth' => WebPush::b64(random_bytes(16)),
        ]);
    }

    private function zadnjaNovost(): array
    {
        return NovostiZvono::novosti()[0];
    }

    public function test_nova_objava_se_salje_jednom(): void
    {
        Http::fake(['fcm.googleapis.com/*' => Http::response('', 201)]);
        $this->travelTo(Carbon::parse($this->zadnjaNovost()['date'])->addDay());

        $user = User::factory()->create();
        $this->pretplata($user);
        $iskljucen = User::factory()->create(['push_obavijesti' => false]);
        $this->pretplata($iskljucen, 'https://fcm.googleapis.com/fcm/send/drugi');

        $this->assertSame([$this->zadnjaNovost()['slug'] => 1], PushNovosti::posalji());
        $this->assertSame([], PushNovosti::posalji(), 'druga runda ništa ne šalje');

        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => $r->url() === self::ENDPOINT
            && $r->hasHeader('Content-Encoding', 'aes128gcm')
            && str_starts_with($r->header('Authorization')[0], 'vapid t='));
    }

    public function test_stare_objave_se_ne_salju(): void
    {
        Http::fake();
        $this->travelTo(Carbon::parse($this->zadnjaNovost()['date'])->addDays(10));
        $this->pretplata(User::factory()->create());

        $this->assertSame([], PushNovosti::posalji());
        Http::assertNothingSent();
    }

    public function test_istekla_pretplata_se_brise(): void
    {
        Http::fake(['fcm.googleapis.com/*' => Http::response('', 410)]);
        $this->travelTo(Carbon::parse($this->zadnjaNovost()['date']));
        $p = $this->pretplata(User::factory()->create());

        PushNovosti::posalji();
        $this->assertModelMissing($p);
    }

    public function test_pretplata_preglednika_i_mobitela(): void
    {
        $this->postJson('/push/pretplata', [])->assertUnauthorized();

        $user = User::factory()->create();
        $this->actingAs($user);
        $kljuc = ['p256dh' => str_repeat('A', 87), 'auth' => str_repeat('B', 22)];

        $this->postJson('/push/pretplata', ['endpoint' => 'https://zli.example.com/x', 'keys' => $kljuc])->assertStatus(422);
        $this->postJson('/push/pretplata', ['endpoint' => self::ENDPOINT, 'keys' => $kljuc])->assertOk();
        $this->assertSame(1, $user->pushPretplate()->count());

        $this->postJson('/push/mobile/device', ['token' => str_repeat('t', 40)])->assertOk();
        $this->postJson('/push/mobile/device', ['token' => str_repeat('t', 40)])->assertOk();
        $this->assertSame(1, $user->mobilePushDevices()->count(), 'isti token se ne duplicira');

        $this->deleteJson('/push/pretplata', ['endpoint' => self::ENDPOINT])->assertOk();
        $this->assertSame(0, $user->pushPretplate()->count());
    }

    public function test_iskljucivanje_u_postavkama_brise_uredjaje(): void
    {
        $user = User::factory()->create();
        $tvrtka = Tvrtka::create(['naziv' => 'Obrt', 'oib' => '12345678903']);
        $tvrtka->users()->attach($user);
        $this->pretplata($user);
        MobilePushDevice::create(['user_id' => $user->id, 'token' => str_repeat('t', 40),
            'token_hash' => hash('sha256', 't'), 'session_hash' => hash('sha256', 's')]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($tvrtka);

        $this->get(Postavke::getUrl())->assertOk()
            ->assertSee('name="placko-native-push" data-user="'.$user->id.'" data-enabled="1"', false)
            ->assertSee('name="placko-push"', false)->assertSee('Obavijesti o novostima');

        Livewire::test(Postavke::class)->call('prebaciPushObavijesti');
        $user->refresh();
        $this->assertFalse($user->push_obavijesti);
        $this->assertSame(0, $user->pushPretplate()->count() + $user->mobilePushDevices()->count());

        $this->postJson('/push/mobile/device', ['token' => str_repeat('t', 40)])->assertForbidden();
    }

    public function test_brisanje_racuna_brise_pretplate(): void
    {
        $user = User::factory()->create();
        $p = $this->pretplata($user);

        BrisanjeRacuna::obrisi($user);
        $this->assertModelMissing($p);
    }
}
