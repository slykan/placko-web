<?php

namespace Tests\Feature;

use App\Mail\KontaktMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class KontaktTest extends TestCase
{
    private function podaci(array $izmjene = []): array
    {
        return array_merge([
            'ime' => 'Ana Anić', 'email' => 'ana@example.com', 'tema' => 'podrska',
            'poruka' => 'Ne mogu poslati račun mailom, javlja grešku.', 'privatnost' => '1', 'kopija' => '1',
            '_t' => encrypt(time() - 30),
        ], $izmjene);
    }

    public function test_stranica_ima_obrazac_s_temama(): void
    {
        $this->get('/kontakt?tema=nadogradnja')->assertOk()
            ->assertSee('Nadogradnja računa')->assertSee('Tehnička podrška')->assertSee('Ostala pitanja')
            ->assertSee('value="nadogradnja" checked', false)
            ->assertSee('name="privatnost"', false);
    }

    public function test_poruka_ide_adminu_i_kopija_posiljatelju(): void
    {
        Mail::fake();
        $this->post('/kontakt', $this->podaci())->assertRedirect('/kontakt')->assertSessionHas('kontakt_poslano');

        Mail::assertSent(KontaktMail::class, fn ($m) => $m->zaAdmina && $m->hasTo('placko@placko.app') && $m->hasReplyTo('ana@example.com'));
        Mail::assertSent(KontaktMail::class, fn ($m) => ! $m->zaAdmina && $m->hasTo('ana@example.com'));
        $this->assertStringContainsString('Ne mogu poslati račun', (new KontaktMail(['ime' => 'Ana', 'email' => 'a@b.hr', 'tema' => 'Tehnička podrška', 'poruka' => 'Ne mogu poslati račun', 'korisnik' => null, 'vrijeme' => '10.11.2026. 12:00'], true))->render());
    }

    public function test_bez_kopije_ide_samo_adminu(): void
    {
        Mail::fake();
        $this->post('/kontakt', $this->podaci(['kopija' => null]))->assertRedirect('/kontakt');
        Mail::assertSentCount(1);
    }

    public function test_privola_tema_i_poruka_su_obavezne(): void
    {
        Mail::fake();
        $this->from('/kontakt')->post('/kontakt', $this->podaci(['privatnost' => null, 'tema' => 'xyz', 'poruka' => 'kratko']))
            ->assertSessionHasErrors(['privatnost', 'tema', 'poruka']);
        Mail::assertNothingSent();
    }

    public function test_robot_ne_salje_nista(): void
    {
        Mail::fake();
        $this->post('/kontakt', $this->podaci(['web_stranica' => 'http://spam.example']))->assertSessionHas('kontakt_poslano');
        $this->post('/kontakt', $this->podaci(['_t' => encrypt(time())]))->assertSessionHas('kontakt_poslano');
        $this->post('/kontakt', $this->podaci(['_t' => 'krivo']))->assertSessionHas('kontakt_poslano');
        Mail::assertNothingSent();
    }
}
