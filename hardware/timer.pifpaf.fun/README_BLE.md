# SG Timer BLE Reader - MicroPython

Aplikacja do odczytu danych z Smart Shot Timer (SG-SST4) przez BLE na Raspberry Pi Pico 2 W.

## Wymagania

- Raspberry Pi Pico 2 W (RP2350)
- MicroPython firmware z obsługą BLE (aioble)
- Urządzenie SG Timer (Sport lub GO)

## Instalacja

1. Wgraj najnowszy firmware MicroPython na Pico 2 W
2. Skopiuj plik `sg_timer_ble.py` na urządzenie
3. Uruchom aplikację

## Użycie

### Podstawowy odczyt wszystkich danych

```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def main():
    timer = SGTimerBLE()

    # Połącz się z urządzeniem
    if await timer.scan_and_connect():
        await timer.discover_services()
        await timer.read_all_data()
        await timer.disconnect()

asyncio.run(main())
```

### Nasłuchiwanie eventów w czasie rzeczywistym

```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def main():
    timer = SGTimerBLE()

    if await timer.scan_and_connect():
        await timer.discover_services()
        await timer.subscribe_to_events()  # Nasłuchuje w pętli
        await timer.disconnect()

asyncio.run(main())
```

### Odczyt konkretnych danych

```python
# Tylko wersja API
version = await timer.read_api_version()

# Tylko czas
unix_time = await timer.read_unix_time()

# Tylko konfiguracja PAR
par_config = await timer.read_par_setup()

# Lista sesji
sessions = await timer.read_saved_sessions()

# Strzały dla konkretnej sesji
shots = await timer.read_shots_for_session(session_id)
```

## Dane zbierane przez aplikację

### 1. API Version
- Wersja API urządzenia (np. "3.2")

### 2. Unix Time
- Aktualny czas urządzenia (timestamp Unix)
- Wyświetlany w formacie czytelnym

### 3. PAR Setup
- **Start Delay**: opóźnienie startu (w sekundach lub losowe)
- **Time Limit**: limit czasu sesji (0 = bez limitu)
- **Shot Limit**: limit strzałów (0 = bez limitu)

### 4. Saved Sessions
- Lista wszystkich zapisanych sesji
- Każda sesja ma ID (timestamp Unix)
- Wyświetlana z datą i czasem

### 5. Shot List
- Lista strzałów dla wybranej sesji
- Numer strzału i czas (w milisekundach)

### 6. Real-time Events
- **SESSION_STARTED**: rozpoczęcie sesji
- **SESSION_SUSPENDED**: wstrzymanie sesji
- **SESSION_RESUMED**: wznowienie sesji
- **SESSION_STOPPED**: zakończenie sesji
- **SHOT_DETECTED**: wykrycie strzału (z czasem)
- **SESSION_SET_BEGIN**: rozpoczęcie po opóźnieniu

## Przykładowe wyjście

```
==================================================
ZBIERANIE WSZYSTKICH DANYCH Z SG TIMER
==================================================

📌 Wersja API: 3.2

🕐 Czas Unix: 1737835200
   Data: 2026-01-25 12:00:00 UTC

⚙️  Konfiguracja PAR:
   Start Delay: 3.0s
   Time Limit: 180.0s
   Shot Limit: 10 strzałów

💾 Zapisane sesje:
   Sesja ID: 1737830000 (2026-01-25 10:33:20)
   Sesja ID: 1737820000 (2026-01-25 07:46:40)

🎯 Strzały dla sesji 1737830000:
   Strzał #0: 1250ms (1.250s)
   Strzał #1: 2100ms (2.100s)
   Strzał #2: 3450ms (3.450s)

==================================================
ZAKOŃCZONO ODCZYT DANYCH
==================================================
```

## Eventy w czasie rzeczywistym

```
📡 Nasłuchiwanie eventów...

🔔 EVENT: SESSION_STARTED
   Session ID: 1737835200
   Start Delay: 3.0s

🔔 EVENT: SESSION_SET_BEGIN
   Session ID: 1737835200

🔔 EVENT: SHOT_DETECTED
   Session ID: 1737835200
   Shot #0: 1234ms (1.234s)

🔔 EVENT: SHOT_DETECTED
   Session ID: 1737835200
   Shot #1: 2456ms (2.456s)

🔔 EVENT: SESSION_STOPPED
   Session ID: 1737835200
   Total Shots: 2
```

## Charakterystyki BLE

| Nazwa | UUID | Właściwości |
|-------|------|-------------|
| COMMAND | 75200000-... | Write, Notify |
| EVENT | 75200001-... | Notify |
| SAVED_SESSION_ID_LIST | 75200002-... | Read, Write |
| RESERVED | 75200003-... | Read |
| SHOT_LIST | 75200004-... | Read, Write |
| PAR_SETUP | 75200005-... | Read, Write |
| UNIX_TIME | 75200006-... | Read, Write |
| API_VERSION | 7520FFFE-... | Read |

## Uwagi

- Wszystkie wartości wielobajtowe są w formacie Big Endian
- Czasy są w milisekundach lub jednostkach 0.1s (zależnie od kontekstu)
- Session ID to timestamp Unix (sekundy od 1970-01-01)
- Maksymalnie odczytywane jest 50 sesji i 100 strzałów na sesję (limity bezpieczeństwa)

## Rozwiązywanie problemów

### Nie można znaleźć urządzenia
- Upewnij się, że urządzenie jest włączone
- Sprawdź czy BLE jest aktywne na timerze
- Zwiększ timeout skanowania: `scan_and_connect(timeout_ms=20000)`

### Błędy połączenia
- Sprawdź czy urządzenie nie jest połączone z innym klientem BLE
- Zresetuj Pico i spróbuj ponownie
- Sprawdź zasięg (max ~10m)

### Brak niektórych charakterystyk
- Nie wszystkie charakterystyki mogą być dostępne w danym modelu
- Aplikacja kontynuuje działanie pomimo brakujących charakterystyk

## Licencja

MIT License
