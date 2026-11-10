<?php

namespace App\Http\Controllers;

use App\Models\PutniNalog;
use App\Models\PutniNalogTrosak;
use App\Models\Zaposlenik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Putni nalozi na osobnom linku zaposlenika ("Moji nalozi"): zaposlenik upisuje polazak, povratak,
 * kilometre, izvješće i račune (fotografija). Obračun i iznose vidi, ali ih ne mijenja.
 */
class MojiPutniController extends Controller
{
    public function show(string $token, int $putni): View
    {
        $zaposlenik = $this->zaposlenik($token);
        $nalog = $this->nalog($zaposlenik, $putni);

        return view('moji-nalozi.putni', [
            'zaposlenik' => $zaposlenik,
            'tvrtka' => $zaposlenik->tvrtka,
            'nalog' => $nalog,
            'mozeUredivati' => $nalog->status === 'otvoren',
        ]);
    }

    public function spremi(Request $request, string $token, int $putni): RedirectResponse
    {
        $zaposlenik = $this->zaposlenik($token);
        $nalog = $this->nalog($zaposlenik, $putni);
        abort_unless($nalog->status === 'otvoren', 403, 'Nalog je obračunat i više se ne može mijenjati.');

        $d = $request->validate([
            'polazak' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'povratak' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:polazak'],
            'granica_odlazak' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'granica_povratak' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'km_pocetno' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'km_zavrsno' => ['nullable', 'integer', 'min:0', 'max:9999999', 'gte:km_pocetno'],
            'izvjesce' => ['nullable', 'string', 'max:5000'],
            'ukloni' => ['array'],
            'ukloni.*' => ['integer'],
            'trosak_vrsta' => ['nullable', 'required_with:trosak_iznos', 'in:'.implode(',', array_keys(PutniNalog::vrsteTroskova()))],
            'trosak_opis' => ['nullable', 'string', 'max:255'],
            'trosak_iznos' => ['nullable', 'numeric', 'min:0.01', 'max:99999'],
            'trosak_slika' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,pdf', 'max:10240'],
        ], [
            'povratak.after' => 'Povratak mora biti nakon polaska.',
            'km_zavrsno.gte' => 'Brojilo na povratku ne može biti manje od brojila na polasku.',
            'trosak_vrsta.required_with' => 'Odaberi vrstu troška.',
            'trosak_slika.max' => 'Slika računa je prevelika (najviše 10 MB).',
        ]);

        $vrijeme = fn (?string $v) => $v ? Carbon::createFromFormat('Y-m-d\TH:i', $v) : null;

        DB::transaction(function () use ($request, $nalog, $d, $vrijeme) {
            $nalog->fill([
                'polazak_at' => $vrijeme($d['polazak'] ?? null) ?? $nalog->polazak_at,
                'povratak_at' => $vrijeme($d['povratak'] ?? null),
                'km_pocetno' => $d['km_pocetno'] ?? null,
                'km_zavrsno' => $d['km_zavrsno'] ?? null,
                'izvjesce' => $d['izvjesce'] ?? null,
            ]);
            if ($nalog->vrsta === 'inozemni') {
                $nalog->granica_odlazak_at = $vrijeme($d['granica_odlazak'] ?? null);
                $nalog->granica_povratak_at = $vrijeme($d['granica_povratak'] ?? null);
            }
            $nalog->save();

            if (! empty($d['ukloni'])) {
                $nalog->troskovi()->whereIn('id', $d['ukloni'])->get()->each->delete();
            }

            if (! empty($d['trosak_iznos'])) {
                $put = $request->file('trosak_slika')?->store('putni-nalozi/'.$nalog->tvrtka_id, 'local');
                PutniNalogTrosak::create([
                    'putni_nalog_id' => $nalog->id,
                    'vrsta' => $d['trosak_vrsta'],
                    'opis' => $d['trosak_opis'] ?? null,
                    'iznos' => $d['trosak_iznos'],
                    'privitak' => $put,
                    'redni_broj' => (int) $nalog->troskovi()->max('redni_broj') + 1,
                ]);
            }

            $nalog->izracunaj();
        });

        return redirect()->route('moji-nalozi.putni', [$zaposlenik->portal_token, $nalog->id])->with('poruka', 'Spremljeno.');
    }

    private function zaposlenik(string $token): Zaposlenik
    {
        abort_if(strlen($token) < 32, 404);

        $zaposlenik = Zaposlenik::with('tvrtka.users')->where('portal_token', $token)->where('aktivan', true)->firstOrFail();
        abort_unless($zaposlenik->tvrtka->imaRadneNaloge(), 403, 'Putni nalozi trenutno nisu aktivni za ovu tvrtku. Javi se uredu.');

        return $zaposlenik;
    }

    private function nalog(Zaposlenik $zaposlenik, int $id): PutniNalog
    {
        return PutniNalog::with('troskovi')->where('tvrtka_id', $zaposlenik->tvrtka_id)
            ->where('zaposlenik_id', $zaposlenik->id)->findOrFail($id);
    }
}
