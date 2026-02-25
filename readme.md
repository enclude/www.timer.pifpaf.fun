# SG Timer - Monitor sesji strzeleckiej

Aplikacja webowa do odczytu i monitorowania danych z timerów strzeleckich SG Timer poprzez Bluetooth Low Energy (BLE).

Strona: [timer.pifpaf.fun](https://timer.pifpaf.fun) | GitHub: [enclude/www.timer.pifpaf.fun](https://github.com/enclude/www.timer.pifpaf.fun)

## Funkcje

- **Polaczenie Bluetooth** - laczenie z timerem przez Web Bluetooth API
- **Podglad na zywo** - biezacy czas i liczba strzalow w czasie rzeczywistym
- **Sterowanie sesja** - Start / Stop sesji strzeleckiej
- **Historia sesji** - przegladanie sesji zapisanych w urzadzeniu
- **Lista strzalow** - czasy i splity dla wybranej sesji
- **Wyslij do kalkulatora** - przesyla dane serii do [piro-kalkulator.pifpaf.fun](https://piro-kalkulator.pifpaf.fun/) po zakonczeniu sesji
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
   - Rozpoczac sesje przyciskiem "Start", zatrzymac "Stop"
   - Po zakonczeniu sesji kliknac "Wyslij do kalkulatora"
   - Przegladac zapisane sesje i ich strzaly

## Specyfikacja techniczna

### BLE Service UUID
```
7520ffff-14d2-4cda-8b6b-697c554c9311
```

### Charakterystyki BLE
| Charakterystyka | UUID | Opis |
|-----------------|------|------|
| Command | 75200000-... | Wysylanie komend do urzadzenia |
| Event | 75200001-... | Odbieranie zdarzen z urzadzenia |
| Session List | 75200002-... | Lista zapisanych sesji |
| Shot List | 75200004-... | Lista strzalow w sesji |
| Unix Time | 75200006-... | Czas urzadzenia (czas lokalny bez strefy) |
| API Version | 7520fffe-... | Wersja API |

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

Po zakonczeniu sesji przycisk "Wyslij do kalkulatora" otwiera [piro-kalkulator.pifpaf.fun](https://piro-kalkulator.pifpaf.fun/) z parametrami GET:

| Parametr | Opis |
|----------|------|
| `liczba_strzalow` | Liczba strzalow w serii |
| `czas_bazowy` | Czas ostatniego strzalu w sekundach (np. `15.80`) |
| `opis` | Lista strzalow z czasami i splitami (URL-encoded) |

### Wersjonowanie (CI/CD)

GitHub Actions (`version.yml`) generuje `version.php` przy kazdym pushu na `main`.
Stopka wyswietla hash commitu jako klikalny link do GitHub oraz date. Lokalnie
(`APP_COMMIT_HASH = 'dev'`) wersja nie jest wyswietlana.

### Testowane urzadzenia

| Urzadzenie | Firmware |
|------------|----------|
| SG-SST4B00000 | BLE API 3.2 |

## Autor

[pifpaf.fun](https://pifpaf.fun)
