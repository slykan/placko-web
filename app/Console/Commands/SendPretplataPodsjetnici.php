<?php

namespace App\Console\Commands;

use App\Mail\PretplataPodsjetnikMail;
use App\Models\Pretplata;
use App\Models\TvrtkaPostavke;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPretplataPodsjetnici extends Command
{
    protected $signature   = 'pretplate:podsjetnici';
    protected $description = 'Šalje podsjetnik klijentima čije pretplate uskoro ističu';

    public function handle(): void
    {
        $poslano = 0;

        TvrtkaPostavke::whereNotNull('pretplate_dani_upozorenja')->each(function (TvrtkaPostavke $postavke) use (&$poslano) {
            $tvrtka = $postavke->tvrtka;

            if (! $tvrtka) {
                return;
            }

            // SMTP konfiguracija tvrtke
            $smtpKonfiguriran = $postavke->smtp_host && $postavke->smtp_user;

            $dani = array_filter(
                array_map('trim', explode(',', $postavke->pretplate_dani_upozorenja ?? '30,15,1')),
                fn ($d) => is_numeric($d) && $d > 0
            );

            foreach ($dani as $dan) {
                $dan = (int) $dan;
                $datum = now()->addDays($dan)->toDateString();

                Pretplata::where('tvrtka_id', $tvrtka->id)
                    ->where('status', 'aktivna')
                    ->whereDate('datum_isteka', $datum)
                    ->with(['klijent', 'stavke'])
                    ->each(function (Pretplata $pretplata) use ($postavke, $tvrtka, $dan, $smtpKonfiguriran, &$poslano) {
                        $klijent = $pretplata->klijent;

                        if (! $klijent?->email) {
                            return;
                        }

                        $usluga     = $pretplata->stavke->pluck('naziv')->implode(', ') ?: ($pretplata->opis ?? '');
                        $varijable  = [
                            '{klijent}'       => $klijent->naziv,
                            '{usluga}'        => $usluga,
                            '{datum_isteka}'  => $pretplata->datum_isteka->format('d.m.Y.'),
                            '{cijena}'        => number_format((float) $pretplata->ukupno, 2, ',', '.') . ' €',
                            '{opis}'          => $pretplata->opis ?? '',
                            '{tvrtka}'        => $tvrtka->naziv,
                            '{dani}'          => $dan,
                        ];

                        $predlozak = $postavke->pretplate_email_predlozak
                            ?? "Poštovani {klijent},\n\nObavještavamo Vas da Vaša pretplata na uslugu \"{usluga}\" ističe za {dani} dana ({datum_isteka}).\n\nCijena obnove: {cijena}\n\nMolimo kontaktirajte nas za obnovu.\n\nS poštovanjem,\n{tvrtka}";

                        $poruka  = str_replace(array_keys($varijable), array_values($varijable), $predlozak);

                        $subjectPredlozak = $postavke->pretplate_email_subject
                            ?? 'Podsjetnik: pretplata ističe za {dani} dana – {tvrtka}';
                        $subject = str_replace(array_keys($varijable), array_values($varijable), $subjectPredlozak);

                        $cc = $postavke->pretplate_email_cc ?: null;

                        try {
                            if ($smtpKonfiguriran) {
                                config([
                                    'mail.mailers.smtp.host'       => $postavke->smtp_host,
                                    'mail.mailers.smtp.port'       => $postavke->smtp_port ?? 587,
                                    'mail.mailers.smtp.username'   => $postavke->smtp_user,
                                    'mail.mailers.smtp.password'   => $postavke->smtp_pass,
                                    'mail.mailers.smtp.encryption' => $postavke->smtp_sigurnost ?? 'tls',
                                    'mail.from.address'            => $postavke->smtp_from_email ?? $postavke->smtp_user,
                                    'mail.from.name'               => $postavke->smtp_from_name ?? $tvrtka->naziv,
                                ]);
                            }

                            Mail::mailer($smtpKonfiguriran ? 'smtp' : config('mail.default'))
                                ->to($klijent->email)
                                ->send(new PretplataPodsjetnikMail($poruka, $subject, $cc));

                            $poslano++;
                            $this->line("Poslan podsjetnik: {$klijent->naziv} ({$klijent->email}) — za {$dan} dana");
                        } catch (\Throwable $e) {
                            $this->error("Greška za {$klijent->naziv}: " . $e->getMessage());
                        }
                    });
            }
        });

        $this->info("Ukupno poslano: {$poslano}");
    }
}
