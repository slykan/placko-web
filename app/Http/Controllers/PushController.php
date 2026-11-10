<?php

namespace App\Http\Controllers;

use App\Models\MobilePushDevice;
use App\Models\PushPretplata;
use App\Support\WebPush;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pretplate na push obavijesti: preglednik (web push) i Android aplikacija (FCM token).
 * Isti uređaj uvijek pripada korisniku koji je zadnji prijavljen na njemu.
 */
class PushController extends Controller
{
    public function spremi(Request $request): JsonResponse
    {
        $d = $request->validate([
            'endpoint' => 'required|url|max:2000',
            'keys.p256dh' => 'required|string|max:255',
            'keys.auth' => 'required|string|max:64',
        ]);
        abort_unless($request->user()->push_obavijesti, 403);
        abort_unless(WebPush::dozvoljenEndpoint($d['endpoint']), 422, 'Nepoznat push servis.');

        PushPretplata::updateOrCreate(['endpoint_hash' => hash('sha256', $d['endpoint'])], [
            'user_id' => $request->user()->id, 'endpoint' => $d['endpoint'],
            'p256dh' => $d['keys']['p256dh'], 'auth' => $d['keys']['auth'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function ukloni(Request $request): JsonResponse
    {
        $d = $request->validate(['endpoint' => 'required|string|max:2000']);
        PushPretplata::where('user_id', $request->user()->id)->where('endpoint_hash', hash('sha256', $d['endpoint']))->delete();

        return response()->json(['ok' => true]);
    }

    public function mobilni(Request $request): JsonResponse
    {
        $d = $request->validate(['token' => ['required', 'string', 'min:20', 'max:4096']]);
        abort_unless($request->user()->push_obavijesti, 403);
        $sesija = hash('sha256', $request->session()->getId());
        $hash = hash('sha256', $d['token']);
        // Kad Firebase zamijeni token, stari s iste sesije se briše.
        MobilePushDevice::where('user_id', $request->user()->id)->where('session_hash', $sesija)
            ->where('token_hash', '!=', $hash)->delete();
        MobilePushDevice::updateOrCreate(['token_hash' => $hash], [
            'user_id' => $request->user()->id, 'token' => $d['token'], 'session_hash' => $sesija,
        ]);

        return response()->json(['ok' => true]);
    }

    public function proba(Request $request): JsonResponse
    {
        return response()->json(['uredjaja' => WebPush::korisniku($request->user(), 'Plačko',
            'Probna obavijest: obavijesti o novostima rade na ovom uređaju.', url('/admin'))]);
    }
}
