# Problem: "Brak zapisanych sesji" mimo że dane są na timerze

## 🔍 Diagnoza problemu

Jeśli aplikacja mobilna pokazuje sesje, ale nasz skrypt pokazuje "Brak zapisanych sesji", może to mieć kilka przyczyn.

---

## 🧪 KROK 1: Uruchom diagnostykę

Najpierw uruchom skrypt diagnostyczny aby zobaczyć co dokładnie timer zwraca:

```python
import asyncio
from debug_sessions import debug_session_reading

asyncio.run(debug_session_reading())
```

Lub krócej:
```python
import debug_sessions
```

### Co sprawdza diagnostyka?

1. **TEST 1** - Odczyt bez inicjalizacji (raw data)
2. **TEST 2** - Odczyt od końca (0xFFFFFFFF)
3. **TEST 3** - Odczyt od początku (0x00000000)
4. **TEST 4** - Sprawdzenie SHOT_LIST
5. **TEST 5** - Inne charakterystyki

### Interpretacja wyników:

#### ✅ DOBRY WYNIK:
```
TEST 2: Inicjalizacja z 0xFFFFFFFF
[1] Odczyt: 0x67A1B2C3 (1738666691)
    ✓ ID Sesji: 1738666691
    📅 Data: 2026-01-28 15:30:45 UTC
[2] Odczyt: 0x67A1A5F0 (1738663408)
    ✓ ID Sesji: 1738663408
    📅 Data: 2026-01-28 14:36:48 UTC
```
→ **Skrypt działa! Sesje są dostępne.**

#### ❌ ZŁY WYNIK - Scenariusz A (natychmiast koniec):
```
TEST 2: Inicjalizacja z 0xFFFFFFFF
[1] Odczyt: 0xFFFFFFFF (4294967295)
    → Koniec listy (0xFFFFFFFF)
```
→ **Timer od razu zwraca koniec listy = brak sesji w pamięci BLE**

#### ❌ ZŁY WYNIK - Scenariusz B (same zera):
```
TEST 2: Inicjalizacja z 0xFFFFFFFF
[1] Odczyt: 0x00000000 (0)
    → Wartość zerowa
```
→ **Charakterystyka zwraca zera = błąd komunikacji lub niezainicjowana pamięć**

#### ❌ ZŁY WYNIK - Scenariusz C (błąd odczytu):
```
TEST 2: Inicjalizacja z 0xFFFFFFFF
❌ Błąd: [Errno 19] ENODEV
```
→ **Problem z połączeniem BLE**

---

## 🔧 ROZWIĄZANIA (zależnie od wyniku)

### Rozwiązanie 1: Timer ma dane ale w innym "trybie"

**Możliwe przyczyny:**
- Timer może mieć tryb "archiwum" gdzie sesje są zapisane ale niedostępne przez BLE
- Sesje mogą być "zablokowane" dopóki nie zostaną zsynchronizowane z aplikacją
- Timer może wymagać specjalnej komendy "unlock" przed udostępnieniem danych

**Co zrobić:**

1. **Sprawdź w aplikacji mobilnej** czy jest opcja "Sync" lub "Export"
2. **Spróbuj zsynchronizować** dane z aplikacją mobilną
3. **Zrestartuj timer** (wyłącz i włącz)
4. **Sprawdź czy timer jest w trybie "RO"** (Range Officer) vs "Shooter"

### Rozwiązanie 2: Sesje są zapisane lokalnie w timerze, ale nie w pamięci BLE

**Możliwe przyczyny:**
- Timer może mieć dwie pamięci: lokalną i BLE-dostępną
- Sesje mogą wymagać "eksportu" do pamięci BLE

**Co zrobić:**

1. W aplikacji mobilnej poszukaj opcji:
   - "Export to BLE"
   - "Make available via Bluetooth"
   - "Sync"
   - "Upload"

2. Sprawdź ustawienia timera (w menu timera, nie w aplikacji):
   - Czy jest opcja "BLE Storage"?
   - Czy jest "Memory Mode"?

### Rozwiązanie 3: Timer wymaga specjalnej sekwencji inicjalizacji

Niektóre urządzenia BLE wymagają wysłania specjalnej komendy przed udostępnieniem danych.

**Spróbuj wysłać komendę przez COMMAND:**

```python
import asyncio
import struct
from sg_timer_ble import SGTimerBLE

async def try_unlock_sessions():
    timer = SGTimerBLE()

    if await timer.scan_and_connect():
        await timer.discover_services()

        if "COMMAND" in timer.characteristics:
            # Spróbuj różnych komend
            commands_to_try = [
                (0x04, "Unknown 0x04"),
                (0x05, "Unknown 0x05"),
                (0x10, "Unknown 0x10"),
            ]

            for cmd_id, name in commands_to_try:
                print(f"\nPróba: {name}")
                packet = struct.pack('BB', 1, cmd_id)

                try:
                    await timer.characteristics["COMMAND"].write(packet)
                    await asyncio.sleep(0.5)

                    # Spróbuj odczytać sesje
                    sessions = await timer.read_saved_sessions()
                    if sessions:
                        print(f"✓ Znaleziono {len(sessions)} sesji!")
                        break
                except Exception as e:
                    print(f"  Błąd: {e}")

        await timer.disconnect()

asyncio.run(try_unlock_sessions())
```

### Rozwiązanie 4: Aplikacja mobilna używa innego API

**Możliwe:**
- Aplikacja może używać starszej wersji API
- Aplikacja może używać proprietarnego protokołu
- Dokumentacja PDF może być nieaktualna

**Co zrobić:**

1. **Sprawdź wersję aplikacji mobilnej** vs wersję API timera
2. **Poszukaj aktualizacji firmware** timera
3. **Zobacz czy jest nowsza dokumentacja BLE API**

### Rozwiązanie 5: Problem z formatem danych

**Spróbuj alternatywnego parsowania:**

```python
import asyncio
import struct
from sg_timer_ble import SGTimerBLE

async def alternative_parsing():
    timer = SGTimerBLE()

    if await timer.scan_and_connect():
        await timer.discover_services()

        if "SAVED_SESSION_ID_LIST" in timer.characteristics:
            # Inicjalizacja
            await timer.characteristics["SAVED_SESSION_ID_LIST"].write(
                struct.pack('>I', 0xFFFFFFFF)
            )

            await asyncio.sleep(0.2)

            # Próba odczytu z różnymi interpretacjami
            for i in range(5):
                data = await timer.characteristics["SAVED_SESSION_ID_LIST"].read()

                print(f"\n[{i+1}] Raw: {data.hex()}")

                # Big Endian (dokumentacja)
                be_val = struct.unpack('>I', data)[0]
                print(f"    Big Endian: 0x{be_val:08X} ({be_val})")

                # Little Endian (na wypadek błędu w dokumentacji)
                le_val = struct.unpack('<I', data)[0]
                print(f"    Little Endian: 0x{le_val:08X} ({le_val})")

                # Jako 4 bajty
                bytes_val = [f"0x{b:02X}" for b in data]
                print(f"    Bajty: {' '.join(bytes_val)}")

                if be_val == 0xFFFFFFFF and le_val == 0xFFFFFFFF:
                    print("    → Koniec (oba endian)")
                    break

                await asyncio.sleep(0.1)

        await timer.disconnect()

asyncio.run(alternative_parsing())
```

---

## 🎯 NAJCZĘSTSZE ROZWIĄZANIA

### 1. Zrestartuj timer i spróbuj ponownie

```python
# Po restarcie timera:
import run_export
```

### 2. Zsynchronizuj z aplikacją mobilną i spróbuj ponownie

1. Otwórz aplikację mobilną
2. Połącz się z timerem
3. Kliknij "Sync" lub "Refresh"
4. Rozłącz aplikację
5. Uruchom nasz skrypt

### 3. Sprawdź czy sesje są na timerze po rozpoczęciu nowej

```python
import asyncio
from examples_advanced import SGTimerController

async def test_new_session():
    timer = SGTimerController()

    if await timer.scan_and_connect():
        await timer.discover_services()

        print("\n1. Rozpoczynam nową sesję...")
        await timer.start_session()

        print("2. Poczekaj 5 sekund i wykonaj kilka strzałów...")
        await asyncio.sleep(5)

        print("3. Zatrzymuję sesję...")
        await timer.stop_session()

        print("4. Sprawdzam czy sesja się zapisała...")
        await asyncio.sleep(1)

        sessions = await timer.read_saved_sessions()
        print(f"\nWynik: Znaleziono {len(sessions)} sesji")

        await timer.disconnect()

asyncio.run(test_new_session())
```

### 4. Sprawdź czy SHOT_LIST działa (obejście)

Jeśli znasz ID sesji z aplikacji mobilnej:

```python
import asyncio
from sg_timer_ble import SGTimerBLE

async def read_specific_session(session_id):
    timer = SGTimerBLE()

    if await timer.scan_and_connect():
        await timer.discover_services()

        # Spróbuj odczytać strzały bezpośrednio
        shots = await timer.read_shots_for_session(session_id)

        if shots:
            print(f"✓ Znaleziono {len(shots)} strzałów w sesji {session_id}!")
            for num, time_ms in shots:
                print(f"  Strzał {num}: {time_ms}ms")
        else:
            print("Brak strzałów")

        await timer.disconnect()

# Użyj ID sesji z aplikacji mobilnej
asyncio.run(read_specific_session(1738666691))
```

---

## 📞 Jeśli nic nie działa

### Zbierz informacje diagnostyczne:

```python
import asyncio
from debug_sessions import debug_session_reading

asyncio.run(debug_session_reading())
```

Zapisz wyjście i sprawdź:
1. Czy wszystkie testy zwracają 0xFFFFFFFF?
2. Czy są jakieś błędy?
3. Jaka jest wersja API timera?
4. Jaki model timera (Sport/GO)?

### Dodatkowe testy:

1. **Sprawdź czy inne charakterystyki działają:**
   ```python
   import project
   # Wybierz opcję 2 - odczyt wszystkich danych
   ```

2. **Porównaj z aplikacją mobilną:**
   - Jaka nazwa urządzenia?
   - Jaka wersja firmware?
   - Ile sesji pokazuje aplikacja?
   - Czy po zsynchronizowaniu z aplikacją coś się zmienia?

---

## 💡 Możliwe wyjaśnienia

### Teoria 1: Timer ma tryb "archived"
Sesje są na timerze ale oznaczone jako "archived" i niedostępne przez BLE dopóki nie zostaną "unarchived" w aplikacji.

### Teoria 2: Różne wersje protokołu
Aplikacja mobilna używa nowszej/starszej wersji protokołu niż dokumentacja PDF (v3.2).

### Teoria 3: Ograniczenie BLE
Timer udostępnia przez BLE tylko N ostatnich sesji, reszta wymaga USB lub aplikacji.

### Teoria 4: Bug w firmware
Timer ma bug który powoduje że SAVED_SESSION_ID_LIST nie działa poprawnie, ale SHOT_LIST działa jeśli znasz ID.

---

## ✅ Podsumowanie działań

1. ✅ Uruchom `debug_sessions.py` aby zobaczyć surowe dane
2. ✅ Zrestartuj timer
3. ✅ Sprawdź aplikację mobilną (Sync/Export)
4. ✅ Spróbuj utworzyć nową sesję i sprawdź czy się zapisze
5. ✅ Jeśli znasz ID sesji, spróbuj odczytać bezpośrednio przez SHOT_LIST
6. ✅ Sprawdź wersję firmware i aplikacji
7. ✅ Skontaktuj się z producentem timera

---

**Uruchom diagnostykę i daj mi znać co pokazuje!** 🔍
