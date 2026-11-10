<?php

namespace App\Http\Controllers;

use App\Mail\RadniNalogZavrsenMail;
use App\Models\RadniNalog;
use App\Models\RadniNalogRad;
use App\Models\RadniNalogStavka;
use App\Models\Usluga;
use App\Models\Zaposlenik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Osobni link zaposlenika ("Moji nalozi"): bez prijave, pristup preko tajnog tokena.
 * Zaposlenik vidi samo naloge na koje je dodijeljen, bez cijena; upisuje svoje sate, materijal,
 * izvršene radove i potpis klijenta.
 */
class MojiNaloziController extends Controller
{
    public function index(string $token): View
    {
        $zaposlenik = $this->zaposlenik($token);

        $nalozi = $zaposlenik->radniNalozi()
            ->with('klijent')
            ->orderByRaw("case when status in ('otvoren','u_tijeku') then 0 else 1 end")
            ->orderByRaw('rok is null')
            ->orderBy('rok')
            ->latest('datum_otvaranja')
            ->limit(100)
            ->get();

        return view('moji-nalozi.index', [
            'zaposlenik' => $zaposlenik,
            'tvrtka' => $zaposlenik->tvrtka,
            'otvoreni' => $nalozi->filter->jeOtvoren()->values(),
            'zatvoreni' => $nalozi->reject->jeOtvoren()->values(),
            'putni' => $zaposlenik->putniNalozi()->whereIn('status', ['otvoren', 'obracunat'])->latest('datum_naloga')->limit(30)->get(),
        ]);
    }

    public function show(string $token, int $nalog): View
    {
        $zaposlenik = $this->zaposlenik($token);
        $nalog = $this->nalog($zaposlenik, $nalog);

        return view('moji-nalozi.show', [
            'zaposlenik' => $zaposlenik,
            'tvrtka' => $zaposlenik->tvrtka,
            'nalog' => $nalog,
            'prihvacen' => (bool) $nalog->pivot->prihvacen_at,
            'mozeUredivati' => $this->mozeUredivati($nalog),
            'usluge' => Usluga::where('tvrtka_id', $zaposlenik->tvrtka_id)->orderBy('naziv')->get(['id', 'naziv', 'jedinica_mjere']),
        ]);
    }

    public function prihvati(string $token, int $nalog): RedirectResponse
    {
        $zaposlenik = $this->zaposlenik($token);
        $nalog = $this->nalog($zaposlenik, $nalog);

        $this->oznaciPrihvacen($zaposlenik, $nalog);

        return redirect()->route('moji-nalozi.show', [$token, $nalog->id])
            ->with('poruka', 'Nalog je prihvaćen. Sretno s poslom!');
    }

    public function spremi(Request $request, string $token, int $nalog): RedirectResponse
    {
        $zaposlenik = $this->zaposlenik($token);
        $nalog = $this->nalog($zaposlenik, $nalog);

        abort_unless($this->mozeUredivati($nalog), 403, 'Nalog je zaključen i više se ne može mijenjati.');

        $data = $request->validate([
            'izvrseni_radovi' => ['nullable', 'string', 'max:5000'],
            'radovi' => ['array', 'max:100'],
            'radovi.*.id' => ['nullable', 'integer'],
            'radovi.*.dolazak' => ['required', 'date_format:Y-m-d H:i'],
            'radovi.*.sati' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'radovi.*.opis' => ['nullable', 'string', 'max:255'],
            'stavke' => ['array', 'max:100'],
            'stavke.*.id' => ['nullable', 'integer'],
            'stavke.*.naziv' => ['required', 'string', 'max:255'],
            'stavke.*.kolicina' => ['required', 'numeric', 'min:0.001', 'max:99999'],
            'stavke.*.jedinica_mjere' => ['nullable', 'string', 'max:20'],
            'potpis' => ['nullable', 'string', 'max:400000', 'starts_with:data:image/png;base64,'],
            'potpisao' => ['nullable', 'string', 'max:255'],
            'zavrsen' => ['nullable', 'boolean'],
        ], [
            'radovi.*.dolazak.required' => 'Upiši datum i vrijeme dolaska.',
            'radovi.*.dolazak.date_format' => 'Datum i vrijeme dolaska nisu ispravni (npr. 04.11.2026. 08:30).',
            'radovi.*.sati.min' => 'Najmanje 0,25 sati po upisu.',
            'radovi.*.sati.max' => 'Najviše 24 sata po upisu — za više dana dodaj više redaka.',
            'stavke.*.naziv.required' => 'Upiši naziv materijala.',
            'potpis.starts_with' => 'Potpis nije ispravan, pokušaj ponovno.',
        ]);

        $zavrsavaSada = $request->boolean('zavrsen') && $nalog->status !== 'zavrsen';

        DB::transaction(function () use ($data, $nalog, $zaposlenik, $zavrsavaSada) {
            $this->spremiRadove($nalog, $zaposlenik, $data['radovi'] ?? []);
            $this->spremiStavke($nalog, $data['stavke'] ?? []);

            $nalog->izvrseni_radovi = $data['izvrseni_radovi'] ?? null;

            if (! empty($data['potpis'])) {
                $nalog->potpis_klijenta = $data['potpis'];
                $nalog->potpisao = $data['potpisao'] ?? null;
                $nalog->potpisano_at = now();
            } elseif ($nalog->potpis_klijenta && array_key_exists('potpisao', $data)) {
                $nalog->potpisao = $data['potpisao'];
            }

            if ($zavrsavaSada) {
                $nalog->status = 'zavrsen';
            } elseif ($nalog->status === 'otvoren') {
                $nalog->status = 'u_tijeku';
            }

            $nalog->save();
            $nalog->izracunajUkupno();
            $this->oznaciPrihvacen($zaposlenik, $nalog);
        });

        if ($zavrsavaSada) {
            $this->javiVlasniku($nalog, $zaposlenik);
        }

        return redirect()->route('moji-nalozi.show', [$zaposlenik->portal_token, $nalog->id])
            ->with('poruka', $zavrsavaSada ? 'Nalog je spremljen i označen kao završen. Javili smo uredu.' : 'Spremljeno.');
    }

    private function spremiRadove(RadniNalog $nalog, Zaposlenik $zaposlenik, array $radovi): void
    {
        // Zaposlenik uređuje samo svoje upise sati; tuđe vidi, ali ih ne dira.
        $postojeci = $nalog->radovi()->where('zaposlenik_id', $zaposlenik->id)->get()->keyBy('id');
        $zadrzi = [];
        $redni = (int) $nalog->radovi()->max('redni_broj');

        foreach (array_values($radovi) as $r) {
            [$datum, $vrijeme] = explode(' ', $r['dolazak']);
            $vrijednosti = ['datum' => $datum, 'vrijeme_dolaska' => $vrijeme, 'sati' => $r['sati'], 'opis' => $r['opis'] ?? null];

            if (! empty($r['id']) && $postojeci->has((int) $r['id'])) {
                $postojeci[(int) $r['id']]->update($vrijednosti);
                $zadrzi[] = (int) $r['id'];
            } else {
                $zadrzi[] = RadniNalogRad::create($vrijednosti + [
                    'radni_nalog_id' => $nalog->id,
                    'zaposlenik_id' => $zaposlenik->id,
                    'cijena_sata' => $zaposlenik->cijena_sata,
                    'redni_broj' => ++$redni,
                ])->id;
            }
        }

        $nalog->radovi()->where('zaposlenik_id', $zaposlenik->id)->whereNotIn('id', $zadrzi ?: [0])->delete();
    }

    private function spremiStavke(RadniNalog $nalog, array $stavke): void
    {
        $postojece = $nalog->stavke()->get()->keyBy('id');
        $zadrzi = [];

        foreach (array_values($stavke) as $i => $s) {
            $kolicina = (float) $s['kolicina'];
            $jm = $s['jedinica_mjere'] ?? null;

            if (! empty($s['id']) && $postojece->has((int) $s['id'])) {
                // Cijene ostaju kako ih je upisao ured; mijenjaju se samo naziv, količina i JM.
                $stavka = $postojece[(int) $s['id']];
                $stavka->fill(['naziv' => $s['naziv'], 'kolicina' => $kolicina, 'jedinica_mjere' => $jm ?: $stavka->jedinica_mjere, 'redni_broj' => $i + 1]);
            } else {
                $usluga = Usluga::where('tvrtka_id', $nalog->tvrtka_id)->where('naziv', $s['naziv'])->first();
                $stavka = new RadniNalogStavka([
                    'radni_nalog_id' => $nalog->id,
                    'usluga_id' => $usluga?->id,
                    'naziv' => $s['naziv'],
                    'jedinica_mjere' => $jm ?: ($usluga?->jedinica_mjere ?? 'kom'),
                    'kolicina' => $kolicina,
                    'cijena' => $usluga?->cijena ?? 0,
                    'rabat_posto' => 0,
                    'pdv_stopa' => $usluga?->pdv_stopa,
                    'redni_broj' => $i + 1,
                ]);
            }

            $neto = (float) $stavka->cijena * $kolicina * (1 - (float) $stavka->rabat_posto / 100);
            $stavka->ukupno = round($neto * (1 + (float) ($stavka->pdv_stopa ?? 0) / 100), 2);
            $stavka->save();
            $zadrzi[] = $stavka->id;
        }

        $nalog->stavke()->whereNotIn('id', $zadrzi ?: [0])->delete();
    }

    private function oznaciPrihvacen(Zaposlenik $zaposlenik, RadniNalog $nalog): void
    {
        if (! $nalog->pivot?->prihvacen_at) {
            $zaposlenik->radniNalozi()->updateExistingPivot($nalog->id, ['prihvacen_at' => now()]);
        }

        if ($nalog->status === 'otvoren') {
            $nalog->update(['status' => 'u_tijeku']);
        }
    }

    private function javiVlasniku(RadniNalog $nalog, Zaposlenik $zaposlenik): void
    {
        $nalog->load(['tvrtka', 'klijent']);
        $prima = $nalog->tvrtka->email ?: $nalog->tvrtka->users()->value('email');

        if ($prima) {
            try {
                Mail::to($prima)->send(new RadniNalogZavrsenMail($nalog, $zaposlenik));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    private function mozeUredivati(RadniNalog $nalog): bool
    {
        return in_array($nalog->status, ['otvoren', 'u_tijeku', 'zavrsen'], true) && ! $nalog->racun_id;
    }

    private function zaposlenik(string $token): Zaposlenik
    {
        abort_if(strlen($token) < 32, 404);

        $zaposlenik = Zaposlenik::with('tvrtka.users')->where('portal_token', $token)->where('aktivan', true)->firstOrFail();
        abort_unless($zaposlenik->tvrtka->imaRadneNaloge(), 403, 'Radni nalozi trenutno nisu aktivni za ovu tvrtku. Javi se uredu.');

        return $zaposlenik;
    }

    private function nalog(Zaposlenik $zaposlenik, int $id): RadniNalog
    {
        return $zaposlenik->radniNalozi()
            ->with(['klijent', 'stavke', 'radovi.zaposlenik', 'zaposlenici', 'nositelj'])
            ->findOrFail($id);
    }
}
