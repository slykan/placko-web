# Plačko Android — prototip 0.3

Prva Android osnova, prilagođena iz AgroLog projekta. Otvara postojeći
`https://placko.app/admin`; korisnici, baza i poslovna logika ostaju na serveru.
Verzija 0.3 otvara PDF odmah unutar aplikacije, bez automatskog spremanja u Preuzimanja.
Pregled ima povećavanje s dva prsta, pomicanje, listanje stranica te Spremi, Podijeli i Ispiši.
Promjena je samo u APK-u: ne traži nove serverske promjene i ne mijenja obični web.
Verzija 0.2 dodala je Google prijavu; korisnik je potvrdio deploy i stvarnu prijavu na mobitelu.

## Uključeno

- Vlastita Plačko ikona, početni ekran i boje.
- WebView s podrškom za postojeću prijavu e-mailom i lozinkom te kolačiće sesije.
- Android Natrag, prilagodba tipkovnici i rubovima ekrana.
- Odabir datoteka za logotip i privitke kroz sistemski odabir.
- Vanjske HTTPS, telefonske i e-mail poveznice otvaraju se u drugim aplikacijama.
- Mrežna pogreška s gumbom za ponovni pokušaj.
- Livewire PDF i izravni HTTPS PDF otvaraju se u ugrađenom pregledu.
- Ostala izravna HTTPS preuzimanja spremaju se u Preuzimanja/Placko.
- Samo dopuštenje za internet; nema AgroLog Firebase konfiguracije ni ključeva.

## Trenutne granice

Ovo je probni APK za Android 10+, potpisan razvojnim ključem.
Identifikator `app.placko.mobile` treba potvrditi prije objave u trgovini.

- PDF pregled zahtijeva Android System WebView s podrškom za WEB_MESSAGE_LISTENER.
- PDF je ograničen na 25 MB. Prikazuje se po jedna stranica radi ograničenja memorije.
- PDF ostaje u privatnom cacheu; datoteke starije od 24 sata brišu se pri pokretanju aplikacije.
  Korisnik trajno sprema dokument tek gumbom Spremi, uz izbor mjesta i naziva.
- Ostali Livewire izvozi poput XML-a i CSV-a još nisu povezani s Androidovim spremanjem.
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

JS provjere: `node --test mobile/tests/pdf-downloads.test.cjs` iz korijena projekta.
Provjera na emulatoru/uređaju: `gradlew.bat connectedDebugAndroidTest` iz `mobile/android`.

## PDF pregled 0.3

Pokriva sve postojeće Filament/Livewire PDF gumbe: računi, ponude, primke,
radni nalozi (interni i za klijenta, u popisu i uređivanju), PO-SD i IRA izvještaji,
uključujući iste izvještaje na stranici Obrasci.

Aplikacija pri početku učitavanja ubacuje `pdf-downloads.js` samo na `https://placko.app`.
Presreće PDF blob iz Livewire gumba prije automatskog preuzimanja i zadržava referencu
dok Livewire uklanja privremeni URL. Prijenos ide u blokovima uz potvrdu svakog bloka.
Android prima poruke samo iz glavnog okvira te domene, provjerava veličinu i PDF zaglavlje,
zatim otvara privatni pregled preko Android PdfRenderera. Nema općeg JavaScript sučelja
za datoteke ili pokretanje Android akcija. Drugi izvori i iframeovi nemaju pristup pregledu.

Spremi otvara Androidov izbor odredišta, Podijeli daje odabranoj aplikaciji privremeno
dopuštenje za čitanje preko FileProvidera, a Ispiši koristi Androidov dijalog za ispis.
Datoteka ne napušta uređaj bez korisnikove akcije dijeljenja/ispisa.

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
Serverske promjene 0.2 korisnik je objavio i potvrdio prijavu na mobitelu.

Tok: WebView dobiva nasumično stanje vezano uz sesiju, potpisana poveznica otvara
Google u pregledniku, a povratak `placko://auth/google` nosi jednokratni kod koji
vrijedi 60 sekundi. Server prihvaća kod samo uz izvornu WebView sesiju. U bazi su
samo hash vrijednosti koda i stanja. Dovršavanje ide POST-om uz CSRF zaštitu;
kod se pri povratku u WebView prenosi URL fragmentom.

Custom scheme može presresti druga aplikacija, čime može omesti povratak, ali bez
izvorne sesije ne može iskoristiti kod. Za trgovinu razmotriti verificirane App Links.

Provjeriti na uređaju: postojeći i novi Google račun, odustajanje, istek koda,
povratak nakon gašenja procesa i nepromijenjenu web prijavu.

Na stvarnom mobitelu provjeriti PDF svakog tipa dokumenta, više stranica, povećavanje,
spremanje i odustajanje, dijeljenje, ispis te povratak u obrazac bez gubitka unosa.
