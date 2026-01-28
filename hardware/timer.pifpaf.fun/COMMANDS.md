# Szybkie Komendy - SG Timer BLE

## 📋 Eksport sesji (główna funkcja)

### Najprostszy sposób (polecany):
```python
import run_export
```

### Z menu:
```python
import project
# Wybierz opcję 1
```

### Bezpośrednio:
```python
import asyncio
from export_sessions import export_all_sessions_to_csv
asyncio.run(export_all_sessions_to_csv())
```

### Jedna linijka:
```python
import asyncio; asyncio.run(__import__('export_sessions').export_all_sessions_to_csv())
```

---

## 🔍 Odczyt i wyświetlenie danych

### Wszystkie dane w konsoli:
```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def show_all():
    timer = SGTimerBLE()
    if await timer.scan_and_connect():
        await timer.discover_services()
        await timer.read_all_data()
        await timer.disconnect()

asyncio.run(show_all())
```

### Tylko lista sesji:
```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def list_sessions():
    timer = SGTimerBLE()
    if await timer.scan_and_connect():
        await timer.discover_services()
        sessions = await timer.read_saved_sessions()
        print(f"\nZnaleziono {len(sessions)} sesji")
        await timer.disconnect()

asyncio.run(list_sessions())
```

### Strzały z konkretnej sesji:
```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def show_session(session_id):
    timer = SGTimerBLE()
    if await timer.scan_and_connect():
        await timer.discover_services()
        shots = await timer.read_shots_for_session(session_id)

        print(f"\nSesja {session_id}: {len(shots)} strzałów")
        for num, time_ms in shots:
            print(f"  Strzał {num}: {time_ms/1000:.3f}s")

        await timer.disconnect()

# Użycie (zamień 1737830000 na właściwy ID sesji):
asyncio.run(show_session(1737830000))
```

---

## 🎯 Nasłuchiwanie eventów na żywo

```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def live_monitor():
    timer = SGTimerBLE()
    if await timer.scan_and_connect():
        await timer.discover_services()
        print("Nasłuchiwanie eventów... (Ctrl+C aby zatrzymać)")
        try:
            await timer.subscribe_to_events()
        except KeyboardInterrupt:
            print("\nZatrzymano")
        await timer.disconnect()

asyncio.run(live_monitor())
```

---

## 🔧 Kontrola sesji

### Rozpocznij sesję:
```python
import asyncio
from examples_advanced import SGTimerController

async def start():
    timer = SGTimerController()
    if await timer.scan_and_connect():
        await timer.discover_services()
        await timer.start_session()
        await timer.disconnect()

asyncio.run(start())
```

### Zatrzymaj sesję:
```python
import asyncio
from examples_advanced import SGTimerController

async def stop():
    timer = SGTimerController()
    if await timer.scan_and_connect():
        await timer.discover_services()
        await timer.stop_session()
        await timer.disconnect()

asyncio.run(stop())
```

### Konfiguruj PAR (opóźnienie 3s, 5 strzałów):
```python
import asyncio
from examples_advanced import SGTimerController

async def setup():
    timer = SGTimerController()
    if await timer.scan_and_connect():
        await timer.discover_services()
        await timer.set_par_setup(
            start_delay=30,    # 3.0s (30 x 0.1s)
            time_limit=0,      # Bez limitu
            shot_limit=5       # 5 strzałów
        )
        await timer.disconnect()

asyncio.run(setup())
```

---

## 🧪 Testowanie

### Test pełny (wszystkie testy):
```python
import asyncio
from test_ble import run_all_tests
asyncio.run(run_all_tests())
```

### Szybki test (podstawy):
```python
import asyncio
from test_ble import quick_test
asyncio.run(quick_test())
```

### Test skanowania:
```python
import asyncio
from test_ble import test_ble_scan
asyncio.run(test_ble_scan())
```

### Test detekcji SG Timer:
```python
import asyncio
from test_ble import test_sg_timer_detection
asyncio.run(test_sg_timer_detection())
```

---

## 💾 Zarządzanie plikami na Pico

### Lista plików:
```python
import os
print("Pliki na Pico:")
for f in os.listdir('/'):
    print(f"  - {f}")
```

### Lista tylko CSV:
```python
import os
csv_files = [f for f in os.listdir('/') if f.endswith('.csv')]
print(f"Pliki CSV ({len(csv_files)}):")
for f in csv_files:
    print(f"  - {f}")
```

### Wyświetl zawartość CSV:
```python
with open('sessions_summary.csv', 'r') as f:
    print(f.read())
```

### Usuń wszystkie CSV:
```python
import os
csv_files = [f for f in os.listdir('/') if f.endswith('.csv')]
for f in csv_files:
    os.remove(f)
    print(f"Usunięto: {f}")
print(f"Usunięto {len(csv_files)} plików")
```

### Sprawdź wolne miejsce:
```python
import os
stat = os.statvfs('/')
free_mb = (stat[0] * stat[3]) / (1024 * 1024)
print(f"Wolne miejsce: {free_mb:.2f} MB")
```

---

## 📊 Szybkie statystyki

### Policz wszystkie sesje i strzały:
```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def stats():
    timer = SGTimerBLE()
    if await timer.scan_and_connect():
        await timer.discover_services()

        sessions = await timer.read_saved_sessions()
        total_shots = 0

        for session_id in sessions:
            shots = await timer.read_shots_for_session(session_id)
            total_shots += len(shots)

        print(f"\nStatystyki:")
        print(f"  Sesje: {len(sessions)}")
        print(f"  Strzały: {total_shots}")
        print(f"  Średnio: {total_shots/len(sessions):.1f} strzałów/sesja")

        await timer.disconnect()

asyncio.run(stats())
```

---

## 🚨 Awaryjne

### Szybki restart BLE:
```python
import bluetooth
ble = bluetooth.BLE()
ble.active(False)
import time
time.sleep(1)
ble.active(True)
print("BLE zrestartowany")
```

### Wymuś garbage collection (zwolnij pamięć):
```python
import gc
gc.collect()
print(f"Wolna pamięć: {gc.mem_free()} bajtów")
```

### Soft reset Pico:
```python
import machine
machine.soft_reset()
```

### Hard reset Pico:
```python
import machine
machine.reset()
```

---

## 📝 Kopiuj-Wklej - Najpopularniejsze

### 1. Eksport wszystkich sesji:
```python
import run_export
```

### 2. Test połączenia:
```python
import asyncio; asyncio.run(__import__('test_ble').quick_test())
```

### 3. Wyświetl wszystkie dane:
```python
import project
# Wybierz opcję 2
```

### 4. Lista plików CSV na Pico:
```python
import os; [print(f) for f in os.listdir('/') if f.endswith('.csv')]
```

### 5. Wolne miejsce:
```python
import os; s=os.statvfs('/'); print(f"{(s[0]*s[3])/1024/1024:.2f} MB")
```

---

## 💡 Wskazówki

- Wszystkie komendy można wklejać bezpośrednio w REPL (konsola Thonny)
- Dla komend async potrzebne jest `asyncio.run()`
- Ctrl+C przerywa działanie programu
- Ctrl+D restartuje REPL
- `help(modul)` wyświetla dokumentację modułu

---

**Zapisz ten plik jako zakładkę - zawiera wszystkie potrzebne komendy!** 📌
