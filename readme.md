# SG Timer - Monitor sesji strzeleckiej

Aplikacja webowa do odczytu i monitorowania danych z timerów strzeleckich SG Timer poprzez Bluetooth Low Energy (BLE).

Strona: [timer.pifpaf.fun](https://timer.pifpaf.fun) | GitHub: [enclude/www.timer.pifpaf.fun](https://github.com/enclude/www.timer.pifpaf.fun)

## Funkcje

- **Polaczenie Bluetooth** - laczenie z timerem przez Web Bluetooth API
- **Podglad na zywo** - biezacy czas i liczba strzalow w czasie rzeczywistym
- **Sterowanie sesja** - Start / Stop sesji strzeleckiej
- **Start z opoznieniem** - Start PAR z losowym opoznieniem 1-4s (PAR_SETUP)
- **Historia sesji** - przegladanie sesji zapisanych w urzadzeniu z liczba strzalow i czasem trwania
- **Cache sesji** - pobranie sesji z ostatnich 24h (wraz z listami strzalow) do pamieci przegladarki (localStorage); pobrane sesje mozna przegladac i wysylac do kalkulatora bez polaczenia BLE — timer pozostaje wolny dla innych
- **Etykiety sesji w cache** - kazdej sesji w cache mozna przypisac nazwe toru i uczestnika (ikona olowka); etykiety sa zapamietywane i uzywane przy wysylaniu do kalkulatora (maja pierwszenstwo nad formularzem "Dane do kalkulatora")
- **Auto-zapis po Stop** - jesli nazwa toru i uczestnik sa wypelnione w "Dane do kalkulatora", zakonczona sesja (czasy, strzaly, splity) automatycznie zapisuje sie w cache z tymi etykietami — idealne przy obsludze kolejnych uczestnikow na tym samym torze
- **Lista strzalow** - czasy i splity dla wybranej sesji
- **Wyslij do kalkulatora** - przesyla dane serii do [piro-kalkulator.pifpaf.fun](https://piro-kalkulator.pifpaf.fun/) — dostepne zarowno po zakonczeniu biezacej sesji, jak i z poziomu historii
- **Zapisz w bazie** - zapisuje wynik (czas i liczba strzalow) bezposrednio do bazy kalkulatora bez przechodzenia przez formularz; po zapisie wyswietla ID wpisu — przyda sie gdy nie liczymy A/C/D, a sam czas i liczba strzalow wystarczy
- **Wersja w stopce** - hash commitu z linkiem do GitHub, generowany automatycznie przez CI

## Wymagania

### Przegladarka
Aplikacja wymaga przegladarki wspierajacej Web Bluetooth API:
- Google Chrome
- Microsoft Edge
- Opera

**Uwaga:** Firefox i Safari nie sa wspierane.

### Kompatybilne urzadzenia
- SG Timer Sport
- SG Timer GO

Aplikacja jest kompatybilna z BLE API w wersji 3.2.

## Jak uzywac

1. Otworz aplikacje w kompatybilnej przegladarce
2. Kliknij "Polacz z timerem" i wybierz urzadzenie (nazwa zaczyna sie od "SG-SST4")
3. Po polaczeniu mozesz:
   - Rozpoczac sesje przyciskiem "Start" (natychmiastowy) lub "Start z opoznieniem" (losowe 1-4s)
   - Zatrzymac sesje przyciskiem "Stop"
   - Po zakonczeniu sesji kliknac "Wyslij do kalkulatora"
   - Przegladac zapisane sesje — lista wyswietla liczbe strzalow i czas trwania
   - Kliknac sesje historyczna, obejrzec strzaly i wyslac do kalkulatora (opis zawiera date sesji)
   - Kliknac "Pobierz sesje do cache" — sesje z ostatnich 24h zapisza sie w przegladarce; po rozlaczeniu mozna je dalej przegladac w karcie "Sesje z cache" i wysylac do kalkulatora
   - Kliknac "Zapisz w bazie" — zapisuje wynik wprost do bazy kalkulatora (bez A/C/D) i wyswietla ID wpisu

## Specyfikacja techniczna

### BLE Service UUID
```
7520ffff-14d2-4cda-8b6b-697c554c9311
```

### Charakterystyki BLE
| Charakterystyka | UUID | Wlasciwosci | Opis |
|-----------------|------|-------------|------|
| Command | 75200000-... | W, N | Wysylanie komend; odpowiedzi jako notyfikacje |
| Event | 75200001-... | N | Odbieranie zdarzen z urzadzenia |
| Session List | 75200002-... | R, W | Lista zapisanych sesji |
| Reserved | 75200003-... | R | Zarezerwowany — nie zapisywac |
| Shot List | 75200004-... | R, W | Lista strzalow w sesji |
| PAR Setup | 75200005-... | R, W | Konfiguracja startu PAR (opoznienie, limit czasu, limit strzalow) |
| Unix Time | 75200006-... | R, W | Czas urzadzenia (czas lokalny bez strefy) |
| API Version | 7520fffe-... | R | Wersja API (ASCII) |

### Komendy
| ID | Nazwa | Opis |
|----|-------|------|
| 0x00 | SESSION_START | Rozpocznij sesje |
| 0x01 | SESSION_SUSPEND | Wstrzymaj sesje |
| 0x02 | SESSION_RESUME | Wznow sesje |
| 0x03 | SESSION_STOP | Zakoncz sesje |

### Zdarzenia
| ID | Nazwa | Opis |
|----|-------|------|
| 0x00 | SESSION_STARTED | Sesja rozpoczeta |
| 0x01 | SESSION_SUSPENDED | Sesja wstrzymana |
| 0x02 | SESSION_RESUMED | Sesja wznowiona |
| 0x03 | SESSION_STOPPED | Sesja zakonczona |
| 0x04 | SHOT_DETECTED | Wykryto strzal |
| 0x05 | SESSION_SET_BEGIN | Poczatek setu sesji |

### Numerowanie strzalow

Urzadzenie wysyla `shotNum` **od 0**. Aplikacja wyswietla strzaly od **1** (`shotNum + 1`).
Pierwszy strzal (`shotNum === 0`) wyswietlany jest bez splitu (`-`).

### Czas urzadzenia

Urzadzenie zapisuje czas lokalny jako Unix timestamp (bez informacji o strefie).
Wyswietlanie uzywa `timeZone: 'UTC'`, aby uniknac podwojnego dodania offsetu przez przegladarke.

### Integracja z kalkulatorem

Przycisk "Wyslij do kalkulatora" otwiera [piro-kalkulator.pifpaf.fun](https://piro-kalkulator.pifpaf.fun/) z parametrami GET:

| Parametr | Opis |
|----------|------|
| `liczba_strzalow` | Liczba strzalow w serii |
| `czas_bazowy` | Czas ostatniego strzalu w sekundach (np. `15.80`) |
| `opis` | Opoznienie startu + lista strzalow z czasami i splitami (URL-encoded) |
| `nazwa_toru` | Nazwa toru z pola "Dane do kalkulatora" (tylko gdy wypelnione) |
| `uczestnik` | Uczestnik z pola "Dane do kalkulatora" (tylko gdy wypelnione) |

Dostepny w dwoch trybach:
- **Biezaca sesja** — pojawia sie po zakonczeniu sesji (SESSION_STOPPED), `opis` zaczyna sie od opoznienia startu (np. `opoznienie startu 2.3s`), dalej lista strzalow
- **Sesja historyczna** — pojawia sie pod lista strzalow wybranej sesji, `opis` zaczyna sie od daty i godziny sesji, a nastepnie opoznienia startu (jesli zapisane w cache)

Opoznienie startu (delay od naciśniecia "Start") pochodzi ze zdarzenia SESSION_STARTED i jest
dolaczane do `opis` dla sesji live oraz sesji z cache (gdzie jest trwale zapisywane). Sesje wczytane
bezposrednio przez BLE ("Wczytaj sesje") nie zawieraja tej informacji — SHOT_LIST jej nie przekazuje.

Po polaczeniu z timerem widoczna jest karta **"Dane do kalkulatora"** z polami "Nazwa toru" i "Uczestnik".
Nazwa toru jest zapamietywana w przegladarce (`localStorage`) i przywracana przy kolejnej wizycie;
uczestnik jest wpisywany kazdorazowo. Oba pola sa opcjonalne — puste nie sa dolaczane do URL.

### Zapis bezposrednio do bazy (bez punktacji)

Przycisk **"Zapisz w bazie"** pozwala zapisac wynik sesji wprost do bazy [piro-kalkulator.pifpaf.fun](https://piro-kalkulator.pifpaf.fun/) bez otwierania kalkulatora i recznego wpisywania A/C/D. Przyda sie gdy interesuje nas sam czas i liczba strzalow, bez pelnej punktacji IPSC.

| Przycisk | Dostepnosc |
|----------|------------|
| "Zapisz w bazie" (biezaca sesja) | pojawia sie po zakonczeniu sesji (razem z "Wyslij do kalkulatora") |
| "Zapisz w bazie" (historia/cache) | pojawia sie pod lista strzalow wybranej sesji |

Po kliknieciu:
1. Przycisk blokuje sie i wyswietla "Zapisywanie..."
2. Dane (liczba strzalow, czas, opis z lista strzalow i splitami, nazwa toru, uczestnik) sa wysylane przez `POST` na `https://piro-kalkulator.pifpaf.fun/api_save.php`
3. Po sukcesie wyswietlane jest **"Zapisano! ID: #123"** — identyfikator wpisu w bazie kalkulatora
4. W razie bledu komunikat pokazuje przyczyne i przycisk odblokowuje sie

Wynik zapisuje sie z zerami dla trafien A/C/D i wszystkich kar (`hit_factor = 0`). Pelne dane (lista strzalow z czasami i splitami) trafiaja do pola `opis`.

### Sygnal tonowy ID (dla Piro Overlay)

Po zapisie w bazie telefon moze odtworzyc otrzymane ID jako sekwencje tonow (marker +
4 cyfry, pasmo 5000-7500 Hz) - aplikacja [Piro Overlay](https://github.com/enclude/congenial-octo-memory),
nakladajaca info o strzalach na wideo, odczytuje ten sygnal z mikrofonu kamery i
uzupelnia ID sesji automatycznie, bez recznego wpisywania.

- Checkbox **"Zagraj sygnal ID po zapisie"** (karta "Dane do kalkulatora", domyslnie
  wylaczony, zapamietywany w przegladarce) - gdy zaznaczony, sygnal gra automatycznie
  zaraz po zapisie.
- Przycisk **"🔊 Zagraj sygnal ID"** pojawia sie zawsze przy komunikacie "Zapisano!"
  (obok "Zapisz w bazie", tak przy sesji zywej, jak i historii/cache) - pozwala puscic
  sygnal jeszcze raz, np. gdy kamera nie nagrywala w danym momencie.
- Dziala tylko dla ID 0-9999 (limit 4-cyfrowego protokolu) - dla wiekszych ID sygnal
  nie jest odtwarzany (zamiast odtworzyc ucieta, blednie wygladajaca wartosc).

### PAR_SETUP

Charakterystyka `75200005-…` (R, W), format: `[start_delay(2), time_limit(2), shot_limit(2)]`

| Pole | Wartosc | Znaczenie |
|------|---------|-----------|
| `start_delay` | jednostki 0.1s | `0x0000` = natychmiastowy, `0xFFFF` = losowe 1.0–4.0s |
| `time_limit` | jednostki 0.1s | `0x0000` = bez limitu czasu |
| `shot_limit` | liczba strzalow | `0x0000` = bez limitu strzalow |

Przycisk "Start" zapisuje `[0x00,0x00,0x00,0x00,0x00,0x00]` (zerowe opoznienie), przycisk "Start z opoznieniem" zapisuje `[0xFF,0xFF,0x00,0x00,0x00,0x00]` (losowe 1–4s). Po zapisie PAR_SETUP wymagane jest oddzielne wyslanie komendy SESSION_START.

### Wersjonowanie (CI/CD)

GitHub Actions (`version.yml`) generuje `version.php` przy kazdym pushu na `main`.
Stopka wyswietla hash commitu jako klikalny link do GitHub oraz date. Lokalnie
(`APP_COMMIT_HASH = 'dev'`) wersja nie jest wyswietlana.

### Testowane urzadzenia

| Urzadzenie | Firmware |
|------------|----------|
| SG-SST4B00000 | BLE API 3.2 |

## Historia testow

### 25.02.2026 — pierwsze testy, odkrycie bledow

- Urządzenie: SG-SST4B00000, API 3.2
- Sesje 8-strzalowe
- Odkryte bledy:
  - `shotNum` 0-indexed — strzaly wyswietlane od 0 zamiast od 1
  - `parseBigEndian` zwracal -1 dla `0xFFFFFFFF` (signed 32-bit overflow) — sentinel nie byl wykrywany, lista sesji ladowala 100 pustych wpisow z ID -1 i data 01.01.1970
  - `formatDate` bez `timeZone:'UTC'` — czas urzadzenia wyswietlany o 1h za duzo (CET podwojnie naliczany)

### 26.02.2026 — testy po poprawkach

- Sesja 30 strzalow, czas 38.20s
- Czas urzadzenia: 26.02.2026, 10:40:42 (telefon: 10:41) — **zgodny po poprawce strefy**
- Licznik i komunikat "Sesja zakonczona (30 strzalow)" / "Strzaly: 30" — **zgodne po poprawce numerowania**
- Przycisk "Wyslij do kalkulatora" pojawil sie poprawnie po zakonczeniu sesji
- Dane przekazane do [piro-kalkulator.pifpaf.fun](https://piro-kalkulator.pifpaf.fun/):
  - `czas_bazowy=38.2`, `liczba_strzalow=30`
  - `opis` z pelna lista 30 strzalow z czasami i splitami, np. `1: 16.05s | 2: 19.23s (+3.18s) | ...`
- Kalkulator poprawnie wyswietlil wynik: czas koncowy 38.20s, 0 kar

## Autor

[pifpaf.fun](https://pifpaf.fun)
