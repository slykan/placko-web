<?php

namespace Tests\Feature;

use App\Models\Klijent;
use App\Models\Racun;
use App\Models\Tvrtka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class NaslovnicaBrojkeTest extends TestCase
{
    use RefreshDatabase;

    public function test_brojke_su_iz_baze_zaokruzene_nanize_a_nacrti_se_ne_broje(): void
    {
        Cache::forget('naslovnica-brojke');
        User::factory()->count(137)->create();
        $tvrtka = Tvrtka::create(['naziv' => 'Obrt', 'oib' => '12345678903']);
        $klijent = Klijent::create(['tvrtka_id' => $tvrtka->id, 'naziv' => 'Kupac']);
        foreach (range(1, 14) as $i) {
            Racun::create([
                'tvrtka_id' => $tvrtka->id, 'klijent_id' => $klijent->id, 'broj' => "$i-1-1", 'redni_broj' => $i,
                'godina' => now()->year, 'datum_izdavanja' => today(), 'status' => $i <= 12 ? 'final' : 'nacrt',
            ]);
        }

        $html = $this->get('/')->assertOk()
            ->assertSee('data-brojka="130"', false)
            ->assertSee('data-brojka="12"', false)
            ->assertSee('izdanih računa')
            ->getContent();

        if ($put = getenv('NASLOVNICA_HTML')) {
            file_put_contents($put, $html);
        }
    }
}
