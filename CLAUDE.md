# CLAUDE.md

## Projekt

**SG Timer Monitor** - aplikacja webowa do odczytu i monitorowania sesji strzeleckich z timerów SG Timer przez Bluetooth Low Energy (BLE).

Strona: [timer.pifpaf.fun](https://timer.pifpaf.fun)
GitHub: [github.com/enclude/www.timer.pifpaf.fun](https://github.com/enclude/www.timer.pifpaf.fun)

## Struktura plików

```
index.php                           # Główny plik aplikacji (HTML + CSS + JS + PHP)
sw.js                               # Service worker (tryb offline, cache powłoki)
manifest.json                       # Manifest PWA (instalacja jako aplikacja)
icons/                              # Ikony PWA: 192, 512 i maskable 512 (PNG)
readme.md                           # Dokumentacja projektu (po polsku)
docs/sg_timer_public_bt_api-32.pdf  # Dokumentacja BLE API (hasłem chroniona)
docs/sg_timer_public_bt_api-32.png/ # Strony PDF jako PNG (czytelne)
screenshots/20260225/               # Screenshoty z testów z 25.02.2026
screenshots/20260226/               # Screenshoty z testów z 26.02.2026
```

## Architektura

Aplikacja to **single-file PHP/HTML/CSS/JS** — cała logika znajduje się w `index.php`:
- **PHP** — rok w stopce + wersja z `.git` (funkcja `appVersion()`: hash z `HEAD`/refs/packed-refs,
  data wdrożenia z `filemtime` refa; deploy = cron `git pull` na serwerze, `.htaccess` blokuje
  dostęp HTTP do `.git`; brak `.git` = brak wersji w stopce, np. lokalnie).
  `appVersion()` jest wołana **na samej górze pliku** (`$appVer`, `$appVerTag`), nie w stopce —
  `<head>` i rejestracja service workera potrzebują skróconego hasha (`sw.js?v=<hash>`);
  stopka używa już gotowego `$appVer`. Brak `.git` = `$appVerTag === 'dev'`
- **PWA** — `manifest.json`, `sw.js`, `icons/` (patrz „Tryb offline i PWA")
- **HTML/CSS** — interfejs użytkownika
- **JavaScript** — cała logika Bluetooth i obsługa UI

## BLE (Bluetooth Low Energy)

Kompatybilność: **SG Timer Sport** i **SG Timer GO** (BLE API 3.2)

Prefix nazwy urządzenia: `SG-SST4`

**Service UUID:** `7520ffff-14d2-4cda-8b6b-697c554c9311`

| Charakterystyka | UUID suffix | Opis |
|-----------------|-------------|------|
| Command         | 75200000-…  | Komendy do urządzenia |
| Event           | 75200001-…  | Zdarzenia z urządzenia |
| Session List    | 75200002-…  | Lista sesji (R, W) |
| Reserved        | 75200003-…  | Zarezerwowany (R) — nie zapisywać |
| Shot List       | 75200004-…  | Lista strzałów (R, W) |
| PAR Setup       | 75200005-…  | Konfiguracja PAR: start_delay, time_limit, shot_limit — 3×2 bajty (R, W) |
| Unix Time       | 75200006-…  | Czas urządzenia (R, W) |
| API Version     | 7520fffe-…  | Wersja API |

## Znane zachowania BLE API (potwierdzone testami i oficjalną dokumentacją)

- Urządzenie (`SG-SST4B00000`, API 3.2) wysyła `shotNum` w zdarzeniu `SHOT_DETECTED` **od 0** (0-indexed, potwierdzone w docs)
- Wyświetlanie zawsze wymaga `shotNum + 1` — zarówno w live shots, jak i w tabeli sesji
- Warunek braku splitu dla pierwszego strzału: `shotNum === 0` (nie `=== 1`)
- Sesja wysyła łączną liczbę strzałów w `SESSION_STOPPED` — wartość zgodna z rzeczywistością
- Zapisane sesje: ID sesji = Unix timestamp urządzenia (czas lokalny)
- PAR_SETUP (`75200005-…`): 3×2 bajty `[start_delay(2), time_limit(2), shot_limit(2)]`, czasy w jednostkach 0.1s, shot_limit = liczba strzałów; `start_delay=0xFFFF` = losowe 1–4s; `start_delay=0x0000` = natychmiastowy start; `time_limit=0` i `shot_limit=0` = bez limitu
- Sentinel końca listy sesji/strzałów = `0xFFFFFFFF` — `parseBigEndian` musi zwracać unsigned (`>>> 0`), inaczej porównanie `=== 0xFFFFFFFF` nigdy nie jest spełnione
- `formatDate` używa `timeZone: 'UTC'` — urządzenie zapisuje czas lokalny jako timestamp (bez strefy), bez `UTC` przeglądarka dodaje kolejne +1h
- `SESSION_SET_BEGIN` (0x05) — wysyłane gdy mija opóźnienie PAR i faktyczny pomiar się zaczyna
- COMMAND (`75200000-…`) ma właściwość N (notify) — odpowiedzi na komendy wracają jako notyfikacja na tej samej charakterystyce
- Pakiet SHOT_DETECTED: `[len(1), event_id(1), sess_id(4), shot_num(2), shot_time(4)]` — shot_num to **2 bajty**
- SHOT_LIST read: `[shot_number(2), shot_time(4)]` — 6 bajtów łącznie; sentinel w polu shot_time
- SAVED_SESSION_ID_LIST: zapis `0xFFFFFFFF` → start od najnowszej; odczyty od najnowszej do najstarszej
- Web Bluetooth dopuszcza **tylko jedną operację GATT naraz** na urządzenie — równoległe `readValue`/`writeValue` kończą się błędem `GATT operation failed for unknown reason`; wszystkie operacje GATT muszą iść przez kolejkę `gattExec()`, a sekwencje kursorowe (sessionList/shotList) nie mogą się przeplatać (`stopMetadataLoad()` + `shotsLoadToken`)

## Ustawienia PAR (limit czasu / limit strzałów)

Karta "Ustawienia PAR" (`parCard`, widoczna po połączeniu — tylko gdy charakterystyka
PAR_SETUP jest dostępna) pozwala ustawić **czas maksymalny** (s, jednostki 0.1s na drucie,
max 6553.4) i **limit strzałów** (max 65534); 0 = bez limitu. Wartości pamiętane per
przeglądarka (`localStorage`, klucz `sgtimer_par_setup`). Limity są zapisywane do timera:
- przyciskiem "Zapisz PAR w timerze" (`writeParToTimer()`) — `start_delay` jest wcześniej
  odczytywany z urządzenia i zachowywany (PAR_SETUP to jedna 6-bajtowa ramka),
- przyciskiem "Zresetuj PAR" (`resetParOnTimer()`) — zeruje OBA pola karty (i localStorage)
  i od razu zapisuje 0/0 do timera, żeby limitu nie przywrócił też następny start,
- automatycznie przy **każdym** starcie ze strony (`startNow()` delay=0,
  `startWithRandomDelay()` delay losowy) — `buildParBytes(delayTenths)` dokleja limity
  z pól karty zamiast dawnych zer.

`lastWrittenPar` = limity aktualnie NA URZĄDZENIU (odczyt przy połączeniu w
`readParFromTimer()` + po każdym zapisie; zerowane przy rozłączeniu). Przy
`SESSION_STARTED` kopiowane do `currentSession.parTime/parShots`, stamtąd do cache
(`parTime`/`parShots` przy wpisie sesji, przenoszone przez `applyCachedLabels()`)
i do payloadu zapisu w bazie (`par_time_limit` [s, float] / `par_shot_limit` [int],
dokładane tylko gdy > 0). **Celowo NIE trafiają do `opis`** — Piro Overlay parsuje
z opisu oś czasu strzałów i format opisu musi zostać bez zmian.

## Tryb offline i PWA

Na stanowisku strzeleckim internetu zwykle nie ma, a wszystko poza zapisem do bazy jest
lokalne (Web Bluetooth, podgląd live, cache w `localStorage`, nadawanie i odtwarzanie kodów
tymczasowych). Dlatego aplikacja jest instalowalna i otwiera się bez sieci:

- `manifest.json` — `start_url`/`scope` = `/`, `display: standalone`, `orientation: portrait-primary`,
  ikony z `icons/` (192, 512, maskable 512). W `<head>`: `link rel="manifest"`, `theme-color`
  (`#0f3d3e`), `apple-touch-icon`, `apple-mobile-web-app-*`.
- `sw.js` — service worker, **cache-first z odświeżaniem w tle** (aplikacja ma otwierać się
  natychmiast; nowe wdrożenie łapie kolejne wejście albo precache nowego workera).
  Precache powłoki: `/`, `/manifest.json`, ikony — pojedynczy brakujący plik **nie** przerywa
  instalacji (`cache.add(...).catch(() => {})`). Każda nawigacja w scope trafia pod jeden klucz
  `'/'` (to zawsze ta sama strona PHP, niezależnie od query stringu), a gdy nic nie ma w cache
  i nie ma sieci — fallback na powłokę spod `'/'`.
- Worker **nie dotyka** żądań innych niż `GET` ani cross-origin: POST-y i API kalkulatora
  (`api_save.php`, `api_lookup.php`) zawsze idą po sieci — nieaktualna odpowiedź „co już jest
  w bazie" oznaczałaby duplikaty wpisów.
- Nazwa cache = `sgtimer-<VERSION>`, gdzie `VERSION` to `?v=` z URL-a workera (czyli hash
  commita z `$appVerTag`, `dev` lokalnie). Każde wdrożenie = nowy URL workera = nowy cache;
  `activate` kasuje pozostałe cache `sgtimer-*`, `skipWaiting()` + `clients.claim()`.
- `.htaccess`: `Cache-Control: no-cache, must-revalidate` dla `sw.js` i `manifest.json` —
  przypięty w cache worker trzymałby starą powłokę przy życiu długo po wdrożeniu.

**Baner synchronizacji** (`#syncBanner`, `updateSyncBanner()`) — odpowiada na dwa pytania,
które mają znaczenie na stanowisku ze słabym zasięgiem: czy jestem offline i ile jeszcze
nie dotarło do bazy:
- offline → `alert-warning`: „Tryb offline — timer, kody tymczasowe i cache działają dalej"
  (+ liczba sesji do wysłania, gdy > 0),
- online z zaległościami → `alert-info` + przycisk „Wyślij zaległe do bazy"
  (ten sam `bulkSaveCacheToDatabase()`),
- brak zaległości online → baner ukryty.

`pendingUploadCount()` = sesje w cache, które mają strzały i **nie** mają `dbId`.
`updateSyncBanner()` woła się przy starcie, z `renderCacheCard()`, po nieudanym zapisie
i ze słuchaczy `window` `online`/`offline`. **Nic nie jest wysyłane automatycznie** —
niepilnowana seria POST-ów po chwiejnym łączu to dokładnie to, przed czym broni hurtowy lookup.

## Cache sesji (localStorage)

Przycisk "Pobierz sesje do cache" (`downloadSessionsToCache()`) zapisuje sesje z **dzisiaj**
(od północy czasu urządzenia: `deviceNow - deviceNow % 86400`, domyślnie) lub z ostatnich 24h
(select `cacheRange`) wraz z pełnymi listami strzałów w `localStorage` pod kluczem `sgtimer_session_cache`
(format: `{ savedAt, sessions: [{ sessId, shots: [{num, time}], nazwaToru?, uczestnik?, timerSn?,
tempId?, dbId?, dbEditToken? }] }`, najnowsze pierwsze). `timerSn` = nazwa urządzenia BLE (numer seryjny, zmienna `deviceSerial`)
z chwili pobrania/auto-zapisu — pokazywana przy sesji w karcie cache (dopisywana przez
`textContent`, nie `innerHTML`) i wysyłana jako `timer_sn` przy zapisie do bazy;
`applyCachedLabels()` przenosi ją ze starego cache tak jak etykiety.
Granica (północ / 24h) liczona od **czasu urządzenia** (charakterystyka Unix Time) — ta sama konwencja
czasu lokalnego co ID sesji. Odczyt listy sesji przerywany wcześniej, gdy `sessId < cutoff`
(lista idzie od najnowszej). Karta "Sesje z cache" (`renderCacheCard()`) działa **bez połączenia BLE**
(nie jest ukrywana w `onDisconnected()`), a klik w sesję renderuje strzały przez wspólne
`renderShots()` (używane też przez ścieżkę BLE) — łącznie z eksportem do kalkulatora.
Każdej sesji w cache można przypisać **tor i uczestnika** (ołówek przy pozycji,
`editCachedSessionLabels()` — dwa `prompt()` z prefiltrem z zapisanych etykiet lub formularza
"Dane do kalkulatora"). Etykiety są user-input — renderowane przez `textContent`, nie `innerHTML`.
Przy eksporcie do kalkulatora etykiety sesji mają pierwszeństwo nad formularzem
(per pole — `appendCalcDataParams(params, overrides)`; puste pole etykiety = fallback do formularza).

**Auto-zapis sesji live:** po `SESSION_STOPPED` (`saveLiveSessionToCache()` w `handleSessionStopped`)
sesja trafia do cache z etykietami. Warunek: sesja ma strzały **i** albo oba pola (tor i uczestnik)
w "Dane do kalkulatora" są wypełnione, albo sesji nadano **kod tymczasowy** — wtedy zapis jest
bezwarunkowy, bo kod na nagraniu bez listy strzałów, na którą wskazuje, jest bezwartościowy
(patrz „Kody tymczasowe"). Duplikaty po `sessId` są nadpisywane, lista sortowana od najnowszej,
status pokazuje "· zapisano w cache". Ponowne "Pobierz sesje do cache" nie kasuje etykiet —
`applyCachedLabels()` przenosi je ze starego cache po `sessId` przed zapisem.
`cache.savedAt` = czas ostatniego zapisu (download lub auto-zapis), w UI jako "Zapisano:".

**Hurtowa wysyłka do bazy (dzień zawodów):** przycisk "Wyślij wszystkie do bazy" w karcie cache
(`bulkSaveCacheToDatabase()`) — cel: nie stracić danych, gdy na stanowisku nie ma czasu wpisywać
zawodników; tor/uczestnika uzupełnia się później w kalkulatorze. Przebieg:
1. sesje z cache bez `dbId` grupowane po `timerSn` → `lookupExistingEntries(sn, sessIds)` (POST
   `api_lookup.php` w kalkulatorze, `{timer_sn, sess_ids[]}` → `{found:[{sess_id,id,nazwa_toru,uczestnik}]}`);
   znalezione dostają `dbId`, a etykiety z bazy wypełniają TYLKO puste lokalne (lokalna poprawka,
   np. literówki w nazwisku, musi przeżyć i przy nadpisywaniu trafić do bazy);
   błąd lookupu = **przerwanie** wysyłki (inaczej groziłyby duplikaty);
2. pozostałe (od najstarszej) POST `api_save.php` z `noFormFallback: true` w `buildSavePayload`
   (pola formularza "Dane do kalkulatora" NIE są dopisywane do wszystkich sesji); po każdym
   sukcesie `dbId` + `dbEditToken` zapisywane do cache od razu (odporność na zerwanie połączenia).
Status: "Wysłano nowych: N · już w bazie: M [· błędy: K]". Sygnał ID nie jest odtwarzany.
**Nadpisywanie:** checkbox "Nadpisz sesje już zapisane w bazie" (`inputBulkOverwrite`, niepamiętany)
— sesje z `dbId` też są wysyłane, z `overwrite: true, id, edit_token?` w payloadzie
(`buildSavePayload` dokłada te pola tylko gdy `overrides.overwrite` i są `timer_sn`+`sess_id`);
`api_save.php` robi wtedy UPDATE pól z timera (strzały, czas, opis, PAR), przelicza czas końcowy
i hit factor z istniejącej punktacji, a tor/uczestnika nadpisuje tylko niepustymi wartościami.
Status wtedy "nadpisano: U" zamiast "już w bazie". Ręczny "Zapisz w bazie" dla sesji z `dbId`
pyta `confirm()`: OK = nadpisz, Anuluj = dodaj nowy wpis; `postToDatabase` pokazuje
"Zaktualizowano wpis ID" gdy `data.updated`, link edycji z tokenu z odpowiedzi lub z payloadu.
Poprawka toru/uczestnika ołówkiem (`editCachedSessionLabels`) dla sesji z `dbId` pyta `confirm()`
"Zaktualizować też wpis w bazie?" → `overwriteCachedSessionInDb(entry)` (pojedynczy POST overwrite,
status w `cacheDbStatus`). Ograniczenie serwera: pusta etykieta NIE czyści pola w bazie —
wyczyścić można tylko w `edit.php`.
`dbId` pokazywany w liście cache jako plakietka "w bazie #ID", `dbEditToken` (tylko gdy wpis zapisany
z TEJ przeglądarki — `api_lookup.php` celowo nie zwraca tokenów, bo SN i ID sesji są publiczne w
`wyniki.php`) daje link "✏️ edytuj w bazie" (`buildDbEditUrl()`). Ręczny zapis (live/historia) też
odkłada `dbId`/`dbEditToken` do cache (`markCachedSessionSaved()` przez callback `onSaved` w
`postToDatabase()`); `renderShots()` dla sesji z `dbId` pokazuje "W bazie: ID #…" z tonem i linkiem
edycji, nie blokując ponownego zapisu. `applyCachedLabels()` i `saveLiveSessionToCache()` przenoszą
`dbId`/`dbEditToken` przy nadpisywaniu wpisu; "Wyczyść cache" pyta o potwierdzenie, gdy są tokeny.

**Eksport i import cache do pliku JSON:** przyciski "Eksportuj do pliku" (`exportCacheToFile()`)
i "Wczytaj z pliku" (`importCacheFromFile(file)`, ukryty `input[type=file]`) w karcie cache.
Powód: `localStorage` to jedna przeglądarka na jednym tablecie — wyczyszczone dane witryny
zabrałyby cały dzień zawodów. Eksport zrzuca cały obiekt cache (`sgtimer-cache-RRRR-MM-DD.json`).
Import **SCALA po `sessId`, nigdy nie zastępuje** (plik może pochodzić z innego tabletu):
nowe sesje są dopisywane, a w istniejących uzupełniane są tylko **puste** pola
(`nazwaToru`, `uczestnik`, `timerSn`, `tempId`, `dbEditToken`, liczbowe `startDelay`/`parTime`/
`parShots`, `dbId` i pusta lista strzałów) — lokalna poprawka i lokalne `dbId` mają pierwszeństwo.
Po scaleniu lista sortowana od najnowszej, komunikat "nowych sesji N, scalonych M".

## Integracja z kalkulatorem PiRO

Przyciski "Wyslij do kalkulatora" otwieraja `https://piro-kalkulator.pifpaf.fun/` z parametrami GET:
`liczba_strzalow`, `czas_bazowy`, `opis` oraz opcjonalnie `nazwa_toru` i `uczestnik`
(z karty "Dane do kalkulatora", dolaczane tylko gdy niepuste — `appendCalcDataParams()`).
Nazwa toru jest pamietana per przegladarka w `localStorage` (klucz `sgtimer_nazwa_toru`),
uczestnik nie jest pamietany.

Przyciski "Zapisz w bazie" wysyłają POST na `https://piro-kalkulator.pifpaf.fun/api_save.php`
z JSON `{liczba_strzalow, czas_bazowy, opis, nazwa_toru?, uczestnik?, timer_sn?, sess_id?,
temp_id?, par_time_limit?, par_shot_limit?}` i wyświetlają zwrócone ID wpisu.
Błąd sieci rozróżnia przyczynę: `navigator.onLine === false` → "Brak internetu — sesja czeka
w cache" (na strzelnicy to stan normalny, nie awaria; sesja pójdzie przyciskiem
"Wyślij zaległe do bazy"), w przeciwnym razie "Błąd połączenia"; obie ścieżki odświeżają baner.
Kary i punktacja są zerowe (tylko czas i liczba strzałów). Funkcje: `saveToDatabase()` (live),
`saveHistoryToDatabase()` (historia), wspólna logika w `buildSavePayload()` i `postToDatabase()`.
`timer_sn` = numer seryjny timera (nazwa urządzenia BLE, zmienna `deviceSerial` ustawiana przy
połączeniu i celowo NIE czyszczona przy rozłączeniu; dla sesji z cache priorytet ma `timerSn`
zapisany przy sesji). `sess_id` = ID sesji **na timerze** (unixtime-podobny, czas lokalny
urządzenia) — live z `currentSession.id`, historia/cache z `historySession.sessId`. Oba pola
dokładane tylko gdy dostępne (`buildSavePayload`). Kalkulator zapisuje je w kolumnach
`timer_sn`/`timer_sess_id`, pokazuje w modalu szczegółów `wyniki.php` i pozwala filtrować
wyniki po numerze seryjnym (parametr `sn` w linku udostępniania wyszukiwania).

**Edycja wpisu po zapisie:** `api_save.php` zwraca też `edit_token` (token uprawniający do edycji
WYŁĄCZNIE tego jednego wpisu). Po sukcesie zapisu `postToDatabase()` dokłada obok "🔊 Zagraj sygnał ID"
link "✏️ Edytuj wpis w bazie" (`addDbEditLinkButton()`) prowadzący do
`https://piro-kalkulator.pifpaf.fun/edit.php?edit=<id>&token=<edit_token>` (nowa karta) — otwiera
formularz edycji tego wpisu w kalkulatorze (przeliczenie punktów A/C/D/kar), bez znajomości
głównego klucza edycji kalkulatora. Link działa tylko dla danego wpisu; token jest w kolumnie
`edit_token` bazy kalkulatora.

**Sygnał tonowy ID (dla Piro Overlay):** po sukcesie `postToDatabase()` woła
`addIdToneReplayButton()` (dokłada przycisk "🔊 Zagraj sygnał ID" do statusu zapisu) i — gdy
checkbox "Zapisz w bazie i zagraj sygnał ID po zakończeniu sesji (wymaga internetu)"
w karcie "Dane do kalkulatora" jest zaznaczony (`inputPlayIdTone`, zapamiętywany
w `localStorage` pod `sgtimer_play_id_tone`, domyślnie WYŁĄCZONY) — od razu odtwarza
`playIdTone(data.id)` (kanał 0 protokołu, patrz „Kody tymczasowe i sygnał tonowy (protokół v3)").
Cel: aplikacja Piro Overlay (github.com/enclude/congenial-octo-memory — nakładka na wideo
ze strzelania) może zdekodować ID sesji prosto z mikrofonu kamery, bez ręcznego wpisywania.

**Auto-zapis do bazy przy koncu sesji live:** gdy powyższy checkbox jest zaznaczony,
`handleSessionStopped` (po `SESSION_STOPPED`, gdy sa strzały) sam woła `saveToDatabase()` —
bez klikania "Zapisz w bazie". Zapis do bazy sam odtwarza ton (patrz wyżej), więc zaznaczenie
tego checkboxa włącza od razu obie rzeczy: zapis i sygnał, skracając obsługę stanowiska.
Odznaczony checkbox = bez zmian, zapis tylko ręcznym przyciskiem. Ta ścieżka **wymaga
internetu** — bez niego identyfikatorem na nagraniu jest kod tymczasowy (niżej).

## Kody tymczasowe i sygnał tonowy (protokół v3)

Problem: sygnał ID wymaga wpisu w bazie, czyli internetu — a na stanowisku go nie ma, więc
nagranie zostawało bez żadnego identyfikatora (ID wpisu bywa dostępne dopiero wieczorem).
Rozwiązanie: **kod tymczasowy** nadawany i grany LOKALNIE w chwili zakończenia sesji.

**Kod tymczasowy**
- Format drutowy: 5 cyfr, np. `"30147"` = cyfra stanowiska + 4-cyfrowy licznik; wyświetlany
  jako `3-0147` (`formatTempId()`). Na drut, do payloadu i do bazy idą **gołe cyfry**.
- `nextTempId()` — licznik trzymany w `localStorage` (`sgtimer_temp_counter`, `% 10000`),
  inkrementowany i zapisywany przed użyciem: przeładowanie strony ani crash nie wydadzą
  drugi raz kodu wcześniejszej sesji.
- **Nr stanowiska = kanał ID-tone**: pole `inputStation` (select 1–9) w karcie "Dane do
  kalkulatora", pamiętane w `sgtimer_station`, `getStation()` pilnuje zakresu.
  **0 jest zarezerwowane** dla ID wpisu w bazie, dlatego stanowiska idą od 1 — każde
  stanowisko ma własną przestrzeń kodów i kody się nie zderzają.
- Checkbox "Zagraj kod tymczasowy po zakończeniu sesji (działa bez internetu)"
  (`inputPlayTempTone`, `sgtimer_play_temp_tone`, **domyślnie WŁĄCZONY** — na strzelnicy kod
  tymczasowy to jedyny identyfikator gwarantowany w chwili końca sesji).
- `handleSessionStopped`: gdy checkbox zaznaczony i sesja ma strzały → `nextTempId()` →
  `currentSession.tempId`, kod dopisany do statusu sesji, pokazany w `#liveTempCode`
  z przyciskiem powtórki (`addTempToneReplayButton()`) i **od razu odtworzony**
  (`playTempIdTone()`). Sesja z kodem trafia do cache bezwarunkowo (patrz „Cache sesji").
- `tempId` żyje w cache, jest przenoszony przez `applyCachedLabels()` i przy nadpisywaniu
  wpisu w `saveLiveSessionToCache()`, pokazywany plakietką "kod 3-0147"
  (`.cache-temp-badge`, obrys — żeby nie mylił się z plakietką "w bazie #ID") w karcie cache
  oraz w `renderShots()` dla sesji z historii/cache.
- Do bazy idzie jako `temp_id` w `buildSavePayload()` — walidacja `/^[1-9]\d{4}$/`
  (kanał 0 to nie jest kod tymczasowy), zawsze w formie 5 cyfr, nigdy `3-0147`.
  Kalkulator zapisuje go w kolumnie `temp_id` i wystawia `api.php?temp_id=`, po czym
  Piro Overlay odnajduje po nim wpis (ID z bazy w ogóle nie musi trafić na nagranie).

**Protokół v3** (`playIdToneFrame()`, Web Audio, sinus przez `AudioContext`+`OscillatorNode`).
Ramka: marker 5000 Hz ("tu zaczyna się kod") + **cyfra KANAŁU** + 4 cyfry wartości + cyfra
kontrolna (`idToneChecksum` = suma ważona pozycją **1..5** po wszystkich pięciu cyfrach,
mod 10); każda cyfra to jeden z 10 tonów 5200–7000 Hz (co 200 Hz na cyfrę 0–9), 300 ms ton
+ 50 ms cisza, cała ramka powtórzona 2× (odstęp 300 ms) dla odporności na zakłócenia.
- **kanał 0** — wartość = ID wpisu w bazie kalkulatora (`playIdTone()`, wymaga internetu),
- **kanał 1–9** — kanał = numer stanowiska, wartość = lokalny kod tymczasowy
  (`playTempIdTone()`, przyjmuje `"30147"` i `"3-0147"`).

Tony idą przez kolejkę `toneChain` / `queueIdToneFrame()` i **nigdy się nie nakładają**:
przy auto-zapisie ton ID wystartowałby w środku kodu tymczasowego i oba zdekodowałyby się
jako śmieci. `playIdToneFrame()` zwraca długość ramki w sekundach (0 = nic nie zagrano),
kolejka czeka tę długość + 150 ms.

Pasmo wybrane tak, by NIE kolidować z bzyczkiem shot-timera (2000–4500 Hz) i zmieścić się
pod Nyquistem ekstrakcji audio Piro Overlay (16 kHz → 8000 Hz). Pasmo i czasy wynikają
z pomiaru realnego nagrania DJI z odległym telefonem: tony >7 kHz zanikały w łańcuchu
głośnik → mikrofon → AAC, dłuższy ton przeżywa zjadanie ogona przez AAC, a cyfra kontrolna
pozwala dekoderowi odrzucić błędny odczyt zamiast pobrać cudzą sesję.

**v3 dodał cyfrę kanału i NIE jest kompatybilny z v2** (marker + 4 cyfry + checksuma
z wagami 1–4; v2 też nie był kompatybilny z v1) — zmiana idzie jednocześnie we wszystkich
trzech repozytoriach. Wartość > 9999 (`ID_TONE_MAX_VALUE`) albo kanał spoza 0–9 NIE są
odtwarzane — lepiej nie zagrać nic niż zagrać uciętą, pozornie prawidłową wartość, którą
druga strona zdekoduje jako cudzą sesję. **DEKODER (osobne repo,
`piro_overlay.audio_sync.decode_id_tone` / `_id_tone_checksum`) ORAZ `id_tone.js`
w kalkulatorze MUSZĄ używać identycznych częstotliwości, czasów, kolejności cyfr i wag
checksumy** — zmiana stałych `ID_TONE_*` tutaj wymaga zmiany odpowiadających `_ID_TONE_*`
tam (inaczej repozytoria rozjadą się po cichu na stałych).

## Praca z subagentami (obowiązkowe)

Każde zadanie, które dotyka więcej niż jednego pliku albo wymaga przeszukania repozytorium, prowadź przez subagentów (narzędzie Task/Agent), zamiast czytać wszystko w głównym kontekście:
- **Rozpoznanie** („gdzie jest X", „które pliki dotyczą Y") → subagent typu Explore; główny kontekst dostaje wniosek, nie zrzuty plików.
- **Zmiany w kilku niezależnych obszarach** (albo w kilku repozytoriach naraz: timer / kalkulator / Piro Overlay) → po jednym subagencie na obszar, uruchamiane równolegle w jednej wiadomości.
- **Wspólne protokoły** (np. ID-tone) → najpierw spisz specyfikację i przekaż ją KAŻDEMU subagentowi dosłownie; inaczej repozytoria rozjadą się na stałych.
- Subagent NIE commituje i NIE aktualizuje dokumentacji — commit, push i dokumentację robi sesja główna po zebraniu raportów.

Wyjątek: pojedyncza, znana zmiana w jednym pliku — rób ją bez subagenta.

## Konwencje

- Język interfejsu: **polski**
- Komunikaty i komentarze w kodzie: po angielsku
- Nie używać zewnętrznych bibliotek — aplikacja działa w całości bez zależności
- Favicon: inline SVG data URI w `<head>`; ikony PWA to osobne PNG-i w `icons/` (manifest nie przyjmuje data URI tak dobrze jak plików, a instalacja wymaga 192 i 512 + maskable)
- Brak systemu buildów, brak package.json

## Wymagania przeglądarki

Web Bluetooth API jest wymagane — działa tylko w:
- Chrome
- Edge
- Opera

Firefox i Safari **nie są wspierane**.
