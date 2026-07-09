<?php

namespace App\Http\Controllers;

use App\Mail\PonudaPrihvacenaMail;
use App\Models\Ponuda;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PonudaPrihvatiController extends Controller
{
    public function prihvati(Ponuda $ponuda): View
    {
        $ponuda->load(['tvrtka', 'klijent']);

        if (! $ponuda->prihvacena_at) {
            $ponuda->update(['prihvacena_at' => now()]);

            $prima = $ponuda->tvrtka->email ?: $ponuda->tvrtka->users()->value('email');

            if ($prima) {
                try {
                    Mail::to($prima)->send(new PonudaPrihvacenaMail($ponuda));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return view('ponuda.prihvacena', ['ponuda' => $ponuda]);
    }
}
