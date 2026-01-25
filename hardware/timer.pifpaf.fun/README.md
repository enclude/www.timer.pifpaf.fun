# SG Timer BLE Reader

Kompletna aplikacja MicroPython dla **Raspberry Pi Pico 2 W** do komunikacji z urządzeniem **Smart Shot Timer** (SG-SST4) przez BLE.

## 📋 Opis projektu

Projekt umożliwia:
- ✅ Skanowanie i łączenie z urządzeniem SG Timer przez BLE
- ✅ Odczyt wszystkich dostępnych danych (wersja API, czas, konfiguracja PAR, sesje, strzały)
- ✅ Nasłuchiwanie eventów w czasie rzeczywistym (rozpoczęcie/zakończenie sesji, wykrycie strzału)
- ✅ Kontrolę sesji (start/stop/suspend/resume)
- ✅ Konfigurację parametrów PAR
- ✅ Eksport danych do plików CSV
- ✅ Logowanie sesji na żywo
- ✅ Testy diagnostyczne połączenia BLE

## 🗂️ Struktura projektu

### Pliki główne

| Plik | Opis |
|------|------|
| **[project.py](project.py)** | Główny punkt wejścia - uruchamia aplikację |
| **[sg_timer_ble.py](sg_timer_ble.py)** | Główny moduł BLE z klasą SGTimerBLE |
| **[examples_advanced.py](examples_advanced.py)** | Zaawansowane przykłady (kontrola sesji, komendy) |
| **[data_logger.py](data_logger.py)** | Eksport danych do CSV i logowanie na żywo |
| **[test_ble.py](test_ble.py)** | Testy diagnostyczne połączenia BLE |

### Dokumentacja

| Plik | Opis |
|------|------|
| **[README.md](README.md)** | Ten plik - główny README projektu |
| **[README_BLE.md](README_BLE.md)** | Szczegółowa dokumentacja API BLE |
| **[INSTALACJA.md](INSTALACJA.md)** | Instrukcja instalacji krok po kroku |
| **sg_timer_public_bt_api-32.pdf** | Oficjalna dokumentacja BLE API (wersja 3.2) |

## 🚀 Szybki start

### 1. Instalacja

Postępuj według instrukcji w **[INSTALACJA.md](INSTALACJA.md)**

Krótko:
1. Wgraj MicroPython na Pico 2 W
2. Skopiuj pliki na urządzenie
3. Uruchom `project.py`

### 2. Pierwszy test

Uruchom tester diagnostyczny aby sprawdzić połączenie:

```python
# W REPL lub Thonny:
import test_ble
import asyncio
asyncio.run(test_ble.run_all_tests())
```

### 3. Podstawowe użycie

```python
# Uruchom główną aplikację
import project
```

Aplikacja automatycznie:
- Przeskanuje urządzenia BLE
- Połączy się z SG Timer
- Odczyta wszystkie dostępne dane
- Wyświetli je w konsoli

## 📚 Przykłady użycia

### Podstawowy odczyt danych

```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def main():
    timer = SGTimerBLE()

    if await timer.scan_and_connect():
        await timer.discover_services()
        await timer.read_all_data()
        await timer.disconnect()

asyncio.run(main())
```

### Kontrola sesji

```python
from examples_advanced import SGTimerController

async def session_control():
    timer = SGTimerController()

    if await timer.scan_and_connect():
        await timer.discover_services()

        # Ustaw parametry
        await timer.set_par_setup(start_delay=30, shot_limit=5)

        # Rozpocznij sesję
        await timer.start_session()

        # ... poczekaj ...

        # Zatrzymaj sesję
        await timer.stop_session()

        await timer.disconnect()

asyncio.run(session_control())
```

### Eksport danych do CSV

```python
from data_logger import SGTimerDataLogger

async def export_data():
    logger = SGTimerDataLogger()

    if await logger.scan_and_connect():
        await logger.discover_services()

        # Eksportuj wszystkie sesje
        await logger.export_all_sessions()

        # Lub stwórz raport podsumowujący
        await logger.create_summary_report()

        await logger.disconnect()

asyncio.run(export_data())
```

### Logowanie sesji na żywo

```python
from data_logger import SGTimerDataLogger

async def live_log():
    logger = SGTimerDataLogger()

    if await logger.scan_and_connect():
        await logger.discover_services()

        # Loguj sesję w czasie rzeczywistym do CSV
        await logger.log_live_session()

        await logger.disconnect()

asyncio.run(live_log())
```

## 🎯 Funkcjonalności

### Odczyt danych

- **API Version** - wersja API urządzenia
- **Unix Time** - aktualny czas urządzenia
- **PAR Setup** - konfiguracja sesji (opóźnienie, limity)
- **Saved Sessions** - lista wszystkich zapisanych sesji
- **Shot List** - lista strzałów dla konkretnej sesji

### Kontrola urządzenia

- **Start/Stop/Suspend/Resume** - kontrola sesji
- **Set PAR Setup** - konfiguracja parametrów
- **Set Unix Time** - synchronizacja czasu

### Eventy w czasie rzeczywistym

- **SESSION_STARTED** - rozpoczęcie sesji
- **SESSION_SUSPENDED** - wstrzymanie
- **SESSION_RESUMED** - wznowienie
- **SESSION_STOPPED** - zakończenie
- **SHOT_DETECTED** - wykrycie strzału (z czasem)
- **SESSION_SET_BEGIN** - start po opóźnieniu

### Eksport i logowanie

- Eksport sesji do plików CSV
- Raport podsumowujący wszystkie sesje
- Logowanie sesji na żywo do pliku

## 🔧 Wymagania

### Sprzęt
- Raspberry Pi Pico 2 W (RP2350)
- Kabel USB
- SG Timer Sport lub SG Timer GO

### Oprogramowanie
- MicroPython 1.23.0 lub nowszy
- Biblioteka `aioble` (wbudowana w firmware dla Pico 2 W)

## 📊 Dane zbierane przez aplikację

### Przykładowe wyjście

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

### Przykładowy plik CSV

```csv
Shot_Number,Time_ms,Time_s,Split_ms,Split_s
0,1234,1.234,0,0.000
1,2456,2.456,1222,1.222
2,3678,3.678,1222,1.222
3,5012,5.012,1334,1.334
```

## 🔍 Rozwiązywanie problemów

### Nie można znaleźć urządzenia

1. Sprawdź czy timer jest włączony
2. Sprawdź czy BLE jest aktywne
3. Umieść timer bliżej Pico (< 10m)
4. Zwiększ timeout: `scan_and_connect(timeout_ms=20000)`

### Błędy połączenia

1. Upewnij się że timer nie jest połączony z innym urządzeniem
2. Zrestartuj Pico i Timer
3. Uruchom tester: `python test_ble.py`

### Brakujące charakterystyki

Niektóre charakterystyki mogą być niedostępne w zależności od modelu timera. Aplikacja kontynuuje działanie pomimo brakujących charakterystyk.

## 📖 Szczegółowa dokumentacja

- **[README_BLE.md](README_BLE.md)** - Szczegółowa dokumentacja API BLE i wszystkich funkcji
- **[INSTALACJA.md](INSTALACJA.md)** - Kompletna instrukcja instalacji
- **sg_timer_public_bt_api-32.pdf** - Oficjalna specyfikacja protokołu BLE

## 🧪 Testowanie

Uruchom testy diagnostyczne:

```python
import asyncio
import test_ble

# Wszystkie testy
asyncio.run(test_ble.run_all_tests())

# Szybki test
asyncio.run(test_ble.quick_test())

# Konkretny test
asyncio.run(test_ble.test_sg_timer_detection())
```

## 🎓 Przykłady zaawansowane

Zobacz **[examples_advanced.py](examples_advanced.py)** dla:

1. Kontroli sesji (start/stop/suspend/resume)
2. Konfiguracji parametrów PAR
3. Monitoringu sesji na żywo
4. Synchronizacji czasu
5. Pełnego workflow treningowego

## 📝 Struktura kodu

```
sg_timer_ble.py
├── SGTimerBLE (klasa bazowa)
│   ├── scan_and_connect()
│   ├── discover_services()
│   ├── read_api_version()
│   ├── read_unix_time()
│   ├── read_par_setup()
│   ├── read_saved_sessions()
│   ├── read_shots_for_session()
│   └── subscribe_to_events()

examples_advanced.py
└── SGTimerController (extends SGTimerBLE)
    ├── send_command()
    ├── start_session()
    ├── stop_session()
    ├── set_par_setup()
    └── live_session_monitor()

data_logger.py
└── SGTimerDataLogger (extends SGTimerBLE)
    ├── export_session_to_csv()
    ├── export_all_sessions()
    ├── create_summary_report()
    └── log_live_session()
```

## 🤝 Wsparcie

W razie problemów:
1. Sprawdź logi w konsoli
2. Uruchom tester diagnostyczny (`test_ble.py`)
3. Sprawdź dokumentację w [INSTALACJA.md](INSTALACJA.md)
4. Przeczytaj FAQ w [README_BLE.md](README_BLE.md)

## 📄 Licencja

MIT License

## 🙏 Podziękowania

- Dokumentacja BLE API: SG Timer (wersja 3.2)
- MicroPython: https://micropython.org/
- aioble: https://github.com/micropython/micropython-lib

---

**Powodzenia z Twoim SG Timerem!** 🎯
