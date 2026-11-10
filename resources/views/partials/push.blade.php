{{-- Push obavijesti o novostima. Android aplikacija čita placko-native-push, preglednik placko-push (VAPID ključ). --}}
@auth
    @php
        $pushKorisnik = auth()->user();
        $pushKljuc = $pushKorisnik->push_obavijesti ? \App\Support\WebPush::javniKljuc() : null;
    @endphp
    <meta name="placko-native-push" data-user="{{ $pushKorisnik->id }}" data-enabled="{{ $pushKorisnik->push_obavijesti ? '1' : '0' }}">
    @if ($pushKljuc)
        <meta name="placko-push" content="{{ $pushKljuc }}" data-korisnik="{{ $pushKorisnik->id }}"
              data-spremi="{{ route('push.spremi') }}" data-proba="{{ route('push.proba') }}">
    @endif
    <script>
    (() => {
        const meta = () => document.querySelector('meta[name="placko-push"]');
        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;
        const uBajtove = (b64) => {
            const s = atob(b64.replace(/-/g, '+').replace(/_/g, '/') + '='.repeat((4 - (b64.length % 4)) % 4));
            return Uint8Array.from(s, (c) => c.charCodeAt(0));
        };
        const zahtjev = (metoda, put, podaci) => fetch(put, {
            method: metoda, credentials: 'same-origin', keepalive: true,
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify(podaci),
        });
        const aplikacija = () => / PlackoNative\//.test(navigator.userAgent);
        const podrzano = () => 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
        const ios = () => /iphone|ipad|ipod/i.test(navigator.userAgent);
        const instalirano = () => window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;

        async function pretplati() {
            const m = meta();
            if (! m) return false;
            const reg = await navigator.serviceWorker.register('/sw.js').then(() => navigator.serviceWorker.ready);
            const sub = (await reg.pushManager.getSubscription())
                || await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: uBajtove(m.content) });
            return (await zahtjev('POST', m.dataset.spremi, sub.toJSON())).ok;
        }

        window.plackoPush = {
            /** aplikacija | iskljuceno | nepodrzano | ios-instaliraj | blokirano | nije-ukljuceno | ukljuceno */
            stanje() {
                if (aplikacija()) return 'aplikacija';
                if (! meta()) return 'iskljuceno';
                if (! podrzano()) return ios() && ! instalirano() ? 'ios-instaliraj' : 'nepodrzano';
                if (Notification.permission === 'denied') return 'blokirano';
                return Notification.permission === 'granted' ? 'ukljuceno' : 'nije-ukljuceno';
            },
            async ukljuci() {
                if (await Notification.requestPermission() !== 'granted') return false;
                return pretplati();
            },
            async proba() {
                const r = await zahtjev('POST', meta()?.dataset.proba || '/push/proba', {});
                return r.ok ? (await r.json()).uredjaja : 0;
            },
        };

        // Već dopušteno: pretplata se osvježi i pripadne korisniku koji je sada prijavljen (jednom po sesiji).
        document.addEventListener('DOMContentLoaded', () => {
            const m = meta();
            if (! m || ! podrzano() || Notification.permission !== 'granted') return;
            try {
                if (sessionStorage.getItem('placko-push-' + m.dataset.korisnik)) return;
                sessionStorage.setItem('placko-push-' + m.dataset.korisnik, '1');
            } catch (e) { /* bez pohrane se samo ponovi */ }
            pretplati().catch(() => {});
        });

        // Odjava: preglednik prestaje primati obavijesti, da ih ne vidi sljedeća osoba na istom računalu.
        document.addEventListener('submit', (e) => {
            const f = e.target;
            if (! f.action?.endsWith('/logout') || f.dataset.pushGotovo || ! podrzano()) return;
            e.preventDefault();
            f.dataset.pushGotovo = '1';
            const m = meta();
            const posao = navigator.serviceWorker.getRegistration().then((reg) => reg?.pushManager.getSubscription()).then((sub) => {
                if (! sub) return null;
                if (m) zahtjev('DELETE', m.dataset.spremi, { endpoint: sub.endpoint }).catch(() => {});
                try { sessionStorage.removeItem('placko-push-' + m?.dataset.korisnik); } catch (err) { /* nije važno */ }
                return sub.unsubscribe();
            }).catch(() => {});
            Promise.race([posao, new Promise((r) => setTimeout(r, 800))]).finally(() => f.submit());
        }, true);
    })();
    </script>
@endauth
