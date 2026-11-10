# Plačko Android — prototip 0.2

Prva Android osnova, prilagođena iz AgroLog projekta. Otvara postojeći
`https://placko.app/admin`; korisnici, baza i poslovna logika ostaju na serveru.
Verzija 0.2 dodaje Google prijavu kroz vanjski preglednik i povratak u aplikaciju.
Za nju su potrebne serverske promjene opisane niže; još nisu objavljene na serveru.

## Uključeno

- Vlastita Plačko ikona, početni ekran i boje.
- WebView s podrškom za postojeću prijavu e-mailom i lozinkom te kolačiće sesije.
- Android Natrag, prilagodba tipkovnici i rubovima ekrana.
- Odabir datoteka za logotip i privitke kroz sistemski odabir.
- Vanjske HTTPS, telefonske i e-mail poveznice otvaraju se u drugim aplikacijama.
- Mrežna pogreška s gumbom za ponovni pokušaj.
- Izravna HTTPS preuzimanja s vlastite domene spremaju se u Preuzimanja/Placko.
- Samo dopuštenje za internet; nema AgroLog Firebase konfiguracije ni ključeva.

## Trenutne granice

Ovo je probni APK za Android 10+, potpisan razvojnim ključem.
Identifikator `app.placko.mobile` treba potvrditi prije objave u trgovini.

- Google prijava zahtijeva objavu serverskih promjena i migracije, zatim provjeru
  stvarnim Google računom na mobitelu. Automatizirani testovi koriste zamjenski Google odgovor.
- Filament/Livewire PDF preuzimanja koriste blob/base64 mehanizam koji još nije podržan.
  Za račune i ponude trenutačno koristiti web u pregledniku.
- Ispis kroz `window.print()` još nije povezan s Androidom.
- Nema offline unosa, mobilnih push obavijesti ni iOS projekta.
- Gradle izgradnja i lint ne potvrđuju stvarnu prijavu ili poslovne tokove.
  Na uređaju provjeriti Turnstile, prijavu/odjavu, Zapamti me, odabir tvrtke,
  unos i spremanje računa i ponuda, tablice, tipkovnicu, privitke i prekid mreže.
  Za provjeru koristiti testne podatke bez slanja ili fiskalizacije stvarnih računa.

## Izgradnja

Potreban je JDK 17+ i Android SDK 36. Lokalni `android/local.properties`
sadrži putanju do SDK-a i isključen je iz gita.

```powershell
cd mobile/android
.\gradlew.bat assembleDebug testDebugUnitTest lintDebug --console=plain
```

APK: `android/app/build/outputs/apk/debug/app-debug.apk`.

## Serverske promjene za Google prijavu

Objaviti kroz postojeći git/deploy postupak zajedno:

- `app/Http/Controllers/Auth/GoogleController.php`
- `app/Http/Controllers/Auth/MobileGoogleController.php`
- `app/Support/MobileGoogleLogin.php`
- `routes/web.php`
- `resources/views/auth/mobile-google-*.blade.php`
- `database/migrations/2026_11_10_100000_create_mobile_google_logins.php`

`deploy.sh` već pokreće `php artisan migrate --force` i obnavlja cache.
Koristi se postojeći Google OAuth client i `/auth/google/callback`; tajne nisu u APK-u.
Ova radna kopija nema konfiguriran pristup Plačkovu serveru.

Tok: WebView dobiva nasumično stanje vezano uz sesiju, potpisana poveznica otvara
Google u pregledniku, a povratak `placko://auth/google` nosi jednokratni kod koji
vrijedi 60 sekundi. Server prihvaća kod samo uz izvornu WebView sesiju. U bazi su
samo hash vrijednosti koda i stanja. Dovršavanje ide POST-om uz CSRF zaštitu;
kod se pri povratku u WebView prenosi URL fragmentom.

Custom scheme može presresti druga aplikacija, čime može omesti povratak, ali bez
izvorne sesije ne može iskoristiti kod. Za trgovinu razmotriti verificirane App Links.

Provjeriti na uređaju: postojeći i novi Google račun, odustajanje, istek koda,
povratak nakon gašenja procesa i nepromijenjenu web prijavu.

Sljedeći koraci: objava serverskih promjena, stvarna Google prijava na uređaju,
Livewire PDF preuzimanja i provjera preostalih korisničkih tokova.
