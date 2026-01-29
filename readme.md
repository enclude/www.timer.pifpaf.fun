# SG Timer - Monitor sesji strzeleckiej

Aplikacja webowa do odczytu i monitorowania danych z timerów strzeleckich SG Timer poprzez Bluetooth Low Energy (BLE).

## Funkcje

- **Polaczenie Bluetooth** - laczenie z timerem strzeleckim przez Web Bluetooth API
- **Podglad na zywo** - wyswietlanie biezacego czasu i liczby strzalow w czasie rzeczywistym
- **Sterowanie sesja** - rozpoczynanie i zatrzymywanie sesji strzeleckiej
- **Historia sesji** - przegladanie zapisanych sesji na urzadzeniu
- **Lista strzalow** - szczegolowy widok strzalow z czasami i splitami

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
2. Kliknij przycisk "Polacz z timerem"
3. Wybierz urzadzenie SG Timer z listy (nazwa zaczyna sie od "SG-SST4")
4. Po polaczeniu mozesz:
   - Rozpoczac nowa sesje strzelecka przyciskiem "Start"
   - Zatrzymac sesje przyciskiem "Stop"
   - Przegladac zapisane sesje klikajac "Odswiezaj liste sesji"
   - Wybrac sesje z listy aby zobaczyc szczegoly strzalow

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
| Unix Time | 75200006-... | Czas urzadzenia |
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

## Autor

[pifpaf.fun](https://pifpaf.fun)