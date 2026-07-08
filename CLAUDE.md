# CLAUDE.md

## Projekt

**SG Timer Monitor** - aplikacja webowa do odczytu i monitorowania sesji strzeleckich z timerów SG Timer przez Bluetooth Low Energy (BLE).

Strona: [timer.pifpaf.fun](https://timer.pifpaf.fun)
GitHub: [github.com/enclude/www.timer.pifpaf.fun](https://github.com/enclude/www.timer.pifpaf.fun)

## Struktura plików

```
index.php                           # Główny plik aplikacji (HTML + CSS + JS + PHP)
version.php                         # Generowany przez CI (hash + data commitu); placeholder: 'dev'
readme.md                           # Dokumentacja projektu (po polsku)
docs/sg_timer_public_bt_api-32.pdf  # Dokumentacja BLE API (hasłem chroniona)
docs/sg_timer_public_bt_api-32.png/ # Strony PDF jako PNG (czytelne)
screenshots/20260225/               # Screenshoty z testów z 25.02.2026
screenshots/20260226/               # Screenshoty z testów z 26.02.2026
.github/workflows/version.yml       # GitHub Actions: generuje version.php przy każdym pushu na main
```

## Architektura

Aplikacja to **single-file PHP/HTML/CSS/JS** — cała logika znajduje się w `index.php`:
- **PHP** — rok w stopce + wczytanie `version.php` (hash commitu)
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
- PAR_SETUP (`75200005-…`): 3×2 bajty `[start_delay(2), time_limit(2), shot_limit(2)]`, wartości w jednostkach 0.1s; `start_delay=0xFFFF` = losowe 1–4s; `start_delay=0x0000` = natychmiastowy start; `time_limit=0` i `shot_limit=0` = bez limitu
- Sentinel końca listy sesji/strzałów = `0xFFFFFFFF` — `parseBigEndian` musi zwracać unsigned (`>>> 0`), inaczej porównanie `=== 0xFFFFFFFF` nigdy nie jest spełnione
- `formatDate` używa `timeZone: 'UTC'` — urządzenie zapisuje czas lokalny jako timestamp (bez strefy), bez `UTC` przeglądarka dodaje kolejne +1h
- `SESSION_SET_BEGIN` (0x05) — wysyłane gdy mija opóźnienie PAR i faktyczny pomiar się zaczyna
- COMMAND (`75200000-…`) ma właściwość N (notify) — odpowiedzi na komendy wracają jako notyfikacja na tej samej charakterystyce
- Pakiet SHOT_DETECTED: `[len(1), event_id(1), sess_id(4), shot_num(2), shot_time(4)]` — shot_num to **2 bajty**
- SHOT_LIST read: `[shot_number(2), shot_time(4)]` — 6 bajtów łącznie; sentinel w polu shot_time
- SAVED_SESSION_ID_LIST: zapis `0xFFFFFFFF` → start od najnowszej; odczyty od najnowszej do najstarszej
- Web Bluetooth dopuszcza **tylko jedną operację GATT naraz** na urządzenie — równoległe `readValue`/`writeValue` kończą się błędem `GATT operation failed for unknown reason`; wszystkie operacje GATT muszą iść przez kolejkę `gattExec()`, a sekwencje kursorowe (sessionList/shotList) nie mogą się przeplatać (`stopMetadataLoad()` + `shotsLoadToken`)

## Cache sesji (localStorage)

Przycisk "Pobierz sesje do cache" (`downloadSessionsToCache()`) zapisuje sesje z ostatnich 24h
(wraz z pełnymi listami strzałów) w `localStorage` pod kluczem `sgtimer_session_cache`
(format: `{ savedAt, sessions: [{ sessId, shots: [{num, time}], nazwaToru?, uczestnik?, timerSn? }] }`,
najnowsze pierwsze). `timerSn` = nazwa urządzenia BLE (numer seryjny, zmienna `deviceSerial`)
z chwili pobrania/auto-zapisu — pokazywana przy sesji w karcie cache (dopisywana przez
`textContent`, nie `innerHTML`) i wysyłana jako `timer_sn` przy zapisie do bazy;
`applyCachedLabels()` przenosi ją ze starego cache tak jak etykiety.
Granica 24h liczona od **czasu urządzenia** (charakterystyka Unix Time) — ta sama konwencja
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
sesja trafia do cache z etykietami, ale **tylko gdy oba pola** (tor i uczestnik) w "Dane do kalkulatora"
są wypełnione i sesja ma strzały. Duplikaty po `sessId` są nadpisywane, lista sortowana od najnowszej,
status pokazuje "· zapisano w cache". Ponowne "Pobierz sesje do cache" nie kasuje etykiet —
`applyCachedLabels()` przenosi je ze starego cache po `sessId` przed zapisem.
`cache.savedAt` = czas ostatniego zapisu (download lub auto-zapis), w UI jako "Zapisano:".

## Integracja z kalkulatorem PiRO

Przyciski "Wyslij do kalkulatora" otwieraja `https://piro-kalkulator.pifpaf.fun/` z parametrami GET:
`liczba_strzalow`, `czas_bazowy`, `opis` oraz opcjonalnie `nazwa_toru` i `uczestnik`
(z karty "Dane do kalkulatora", dolaczane tylko gdy niepuste — `appendCalcDataParams()`).
Nazwa toru jest pamietana per przegladarka w `localStorage` (klucz `sgtimer_nazwa_toru`),
uczestnik nie jest pamietany.

Przyciski "Zapisz w bazie" wysyłają POST na `https://piro-kalkulator.pifpaf.fun/api_save.php`
z JSON `{liczba_strzalow, czas_bazowy, opis, nazwa_toru?, uczestnik?, timer_sn?, sess_id?}`
i wyświetlają zwrócone ID wpisu.
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
checkbox "Zagraj sygnał ID po zapisie" w karcie "Dane do kalkulatora" jest zaznaczony
(`inputPlayIdTone`, zapamiętywany w `localStorage` pod `sgtimer_play_id_tone`, domyślnie
WYŁĄCZONY) — od razu odtwarza `playIdTone(data.id)`. Cel: aplikacja Piro Overlay
(github.com/enclude/congenial-octo-memory — nakładka na wideo ze strzelania) może
zdekodować ID sesji prosto z mikrofonu kamery, bez ręcznego wpisywania.

Protokół v2 (`playIdTone`, Web Audio, sinus przez `AudioContext`+`OscillatorNode`): marker
5000 Hz ("tu zaczyna się kod") + 4 tony cyfr + ton cyfry kontrolnej (`idToneChecksum` =
suma ważona pozycją 1–4 mod 10), każdy jeden z 10 tonów 5200–7000 Hz (co 200 Hz na cyfrę
0–9), 300 ms ton + 50 ms cisza, cała sekwencja powtórzona 2× (odstęp 300 ms) dla
odporności na zakłócenia. Pasmo wybrane tak, by NIE kolidować z bzyczkiem shot-timera
(2000–4500 Hz) i zmieścić się pod Nyquistem ekstrakcji audio Piro Overlay (16 kHz →
8000 Hz). Zmiany v2 względem v1 (5250–7500 Hz co 250 Hz, 200 ms, bez checksumy; BEZ
kompatybilności wstecznej) wynikają z pomiaru realnego nagrania DJI z odległym telefonem:
tony >7 kHz zanikały w łańcuchu głośnik → mikrofon → AAC (stąd niższy sufit pasma),
dłuższy ton przeżywa zjadanie ogona przez AAC, a cyfra kontrolna pozwala dekoderowi
odrzucić błędny odczyt zamiast pobrać cudzą sesję.
ID > 9999 (poza zasięgiem 4 cyfr protokołu) NIE jest odtwarzane — `playIdTone` woli nic nie
zagrać niż zagrać ucięte (błędne) ID, które druga strona zdekodowałaby jako pozornie
prawidłowe. **DEKODER (osobne repo, `audio_sync.decode_id_tone` w piro-overlay) MUSI
używać identycznych częstotliwości/czasów** — zmiana stałych `ID_TONE_*` tutaj wymaga
zmiany odpowiadających `_ID_TONE_*` tam.

## Konwencje

- Język interfejsu: **polski**
- Komunikaty i komentarze w kodzie: po angielsku
- Nie używać zewnętrznych bibliotek — aplikacja działa w całości bez zależności
- Favicon: inline SVG data URI w `<head>`
- Brak systemu buildów, brak package.json

## Wymagania przeglądarki

Web Bluetooth API jest wymagane — działa tylko w:
- Chrome
- Edge
- Opera

Firefox i Safari **nie są wspierane**.
