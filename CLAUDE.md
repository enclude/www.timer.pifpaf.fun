# CLAUDE.md

## Projekt

**SG Timer Monitor** - aplikacja webowa do odczytu i monitorowania sesji strzeleckich z timerów SG Timer przez Bluetooth Low Energy (BLE).

Strona: [timer.pifpaf.fun](https://timer.pifpaf.fun)
GitHub: [github.com/enclude/www.timer.pifpaf.fun](https://github.com/enclude/www.timer.pifpaf.fun)

## Struktura plików

```
index.php       # Główny i jedyny plik aplikacji (HTML + CSS + JS + PHP)
readme.md       # Dokumentacja projektu (po polsku)
docs/           # Dokumentacja techniczna (pusta)
```

## Architektura

Aplikacja to **single-file PHP/HTML/CSS/JS** — cała logika znajduje się w `index.php`:
- **PHP** — tylko do wypisania bieżącego roku w stopce
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
| Session List    | 75200002-…  | Lista sesji |
| Shot List       | 75200004-…  | Lista strzałów |
| Unix Time       | 75200006-…  | Czas urządzenia |
| API Version     | 7520fffe-…  | Wersja API |

## Znane zachowania BLE API (na podstawie testów i screenshotów z 2026-02-25)

- Urządzenie (`SG-SST4B00000`, API 3.2) wysyła `shotNum` w zdarzeniu `SHOT_DETECTED` **od 0** (0-indexed)
- Wyświetlanie zawsze wymaga `shotNum + 1` — zarówno w live shots, jak i w tabeli sesji
- Warunek braku splitu dla pierwszego strzału: `shotNum === 0` (nie `=== 1`)
- Sesja wysyła łączną liczbę strzałów w `SESSION_STOPPED` — wartość zgodna z rzeczywistością
- Zapisane sesje: ID sesji = Unix timestamp; nieprawidłowe ID (`0xFFFFFFFF` = sentinel, `-1` = błąd parsowania 32-bit jako signed)

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
