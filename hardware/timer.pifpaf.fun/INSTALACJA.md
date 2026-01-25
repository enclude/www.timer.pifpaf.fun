# Instalacja i konfiguracja - SG Timer BLE Reader

## Wymagania sprzętowe

- **Raspberry Pi Pico 2 W** (RP2350) z obsługą WiFi i BLE
- Kabel USB do podłączenia Pico
- Urządzenie **SG Timer Sport** lub **SG Timer GO**

## Krok 1: Instalacja MicroPython

1. Pobierz najnowszą wersję MicroPython dla Pico 2 W:
   - Wejdź na https://micropython.org/download/RPI_PICO2_W/
   - Pobierz plik `.uf2` (np. `RPI_PICO2_W-20240602-v1.23.0.uf2`)

2. Wgraj firmware na Pico:
   - Przytrzymaj przycisk **BOOTSEL** na Pico
   - Podłącz Pico do komputera przez USB (wciąż trzymając BOOTSEL)
   - Zwolnij BOOTSEL - Pico pojawi się jako dysk USB
   - Skopiuj plik `.uf2` na dysk Pico
   - Pico automatycznie się zrestartuje

3. Sprawdź połączenie:
   - Pico powinno być widoczne jako port szeregowy (COM na Windows, /dev/ttyACM0 na Linux)

## Krok 2: Instalacja Thonny IDE (opcjonalnie)

1. Pobierz i zainstaluj Thonny: https://thonny.org/
2. Uruchom Thonny
3. W prawym dolnym rogu wybierz interpreter: **MicroPython (Raspberry Pi Pico)**

## Krok 3: Przesłanie plików na Pico

### Metoda A: Przez Thonny IDE

1. Otwórz Thonny
2. Podłącz Pico przez USB
3. W menu wybierz: **View** → **Files**
4. W panelu plików zobaczysz dwa obszary:
   - Górny: pliki na komputerze
   - Dolny: pliki na Pico

5. Prześlij pliki na Pico:
   - `sg_timer_ble.py` (główny moduł)
   - `project.py` (punkt wejścia)
   - `examples_advanced.py` (opcjonalnie, przykłady zaawansowane)

6. Kliknij prawym na plik → **Upload to /**

### Metoda B: Przez ampy (narzędzie CLI)

1. Zainstaluj ampy:
   ```bash
   pip install adafruit-ampy
   ```

2. Prześlij pliki:
   ```bash
   ampy --port COM3 put sg_timer_ble.py
   ampy --port COM3 put project.py
   ampy --port COM3 put examples_advanced.py
   ```
   (Zmień COM3 na właściwy port)

### Metoda C: Przez mpremote

1. Zainstaluj mpremote:
   ```bash
   pip install mpremote
   ```

2. Prześlij pliki:
   ```bash
   mpremote connect COM3 fs cp sg_timer_ble.py :
   mpremote connect COM3 fs cp project.py :
   mpremote connect COM3 fs cp examples_advanced.py :
   ```

## Krok 4: Uruchomienie aplikacji

### Opcja 1: Z Thonny IDE

1. Otwórz plik `project.py` na Pico
2. Kliknij przycisk **Run** (zielona strzałka) lub naciśnij F5
3. Obserwuj wyjście w konsoli

### Opcja 2: Z REPL (konsola interaktywna)

1. Połącz się z Pico przez REPL (Thonny lub inny terminal szeregowy)
2. Wykonaj:
   ```python
   import project
   ```

### Opcja 3: Autostart po włączeniu Pico

1. Zmień nazwę `project.py` na `main.py`:
   ```python
   # W Thonny: kliknij prawym na project.py → Rename → main.py
   ```
2. Zrestartuj Pico - aplikacja uruchomi się automatycznie

## Krok 5: Pierwsze uruchomienie

Po uruchomieniu zobaczysz:

```
Skanowanie urządzeń BLE...

Znaleziono urządzenie: SG-SST4A00123
Adres: XX:XX:XX:XX:XX:XX
RSSI: -45 dBm

Łączenie...
Połączono!

Odkrywanie serwisów...
Znaleziono serwis: 7520FFFF-14D2-4CDA-8B6B-697C554C9311
  ✓ COMMAND
  ✓ EVENT
  ✓ SAVED_SESSION_ID_LIST
  ✓ RESERVED
  ✓ SHOT_LIST
  ✓ PAR_SETUP
  ✓ UNIX_TIME
  ✓ API_VERSION

==================================================
ZBIERANIE WSZYSTKICH DANYCH Z SG TIMER
==================================================

📌 Wersja API: 3.2

🕐 Czas Unix: 1737835200
   Data: 2026-01-25 12:00:00 UTC

⚙️  Konfiguracja PAR:
   Start Delay: 3.0s
   Time Limit: Bez limitu
   Shot Limit: Bez limitu

💾 Zapisane sesje:
   (lista sesji...)

==================================================
ZAKOŃCZONO ODCZYT DANYCH
==================================================

Rozłączono
```

## Krok 6: Testowanie połączenia

Jeśli aplikacja nie znajduje urządzenia:

1. **Sprawdź czy urządzenie jest włączone**
   - SG Timer powinien być aktywny
   - BLE powinno być włączone

2. **Sprawdź zasięg**
   - Umieść Pico i Timer blisko siebie (< 5m)

3. **Zwiększ czas skanowania**
   - Edytuj `sg_timer_ble.py`:
   ```python
   if await timer.scan_and_connect(timeout_ms=20000):  # 20 sekund
   ```

4. **Sprawdź czy BLE działa na Pico**
   - W REPL wykonaj:
   ```python
   import aioble
   import bluetooth
   print("BLE OK")
   ```

## Przykłady użycia

### 1. Podstawowy odczyt danych
```python
# Uruchom project.py
```

### 2. Zaawansowane funkcje
```python
# Edytuj examples_advanced.py i uruchom wybrane przykłady
import asyncio
from examples_advanced import example_1_basic_session_control

asyncio.run(example_1_basic_session_control())
```

### 3. Własny skrypt
```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def my_script():
    timer = SGTimerBLE()

    if await timer.scan_and_connect():
        await timer.discover_services()

        # Twój kod tutaj
        await timer.read_api_version()
        await timer.read_saved_sessions()

        await timer.disconnect()

asyncio.run(my_script())
```

## Rozwiązywanie problemów

### Błąd: "ImportError: no module named 'aioble'"

MicroPython dla Pico 2 W powinien mieć wbudowany `aioble`. Jeśli nie:

1. Zaktualizuj firmware do najnowszej wersji
2. Lub zainstaluj mpremote i wykonaj:
   ```bash
   mpremote mip install aioble
   ```

### Błąd: "OSError: [Errno 19] ENODEV"

Problem z BLE. Rozwiązania:
- Zrestartuj Pico (odłącz i podłącz USB)
- Sprawdź czy używasz Pico **2 W** (nie zwykłego Pico lub Pico W)

### Pico się zawiesza podczas skanowania

- Zmniejsz czas skanowania: `scan_and_connect(timeout_ms=5000)`
- Dodaj `gc.collect()` przed skanowaniem:
  ```python
  import gc
  gc.collect()
  await timer.scan_and_connect()
  ```

### Nie widać danych w konsoli

- W Thonny: sprawdź czy w dolnym panelu widzisz "Shell"
- Upewnij się że baudrate jest ustawiony na 115200

## Dodatkowe zasoby

- **Dokumentacja MicroPython**: https://docs.micropython.org/
- **Biblioteka aioble**: https://github.com/micropython/micropython-lib/tree/master/micropython/bluetooth/aioble
- **Dokumentacja BLE API SG Timer**: [sg_timer_public_bt_api-32.pdf](sg_timer_public_bt_api-32.pdf)

## Wsparcie

W razie problemów:
1. Sprawdź logi błędów w konsoli
2. Upewnij się że wszystkie pliki są poprawnie przesłane
3. Zrestartuj zarówno Pico jak i SG Timer
4. Sprawdź czy firmware MicroPython jest aktualny

---

**Powodzenia z pierwszym uruchomieniem!** 🎯
