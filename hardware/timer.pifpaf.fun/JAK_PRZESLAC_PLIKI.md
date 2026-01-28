# Jak przesłać pliki na Raspberry Pi Pico 2 W?

## 🎯 Które pliki musisz przesłać?

**MINIMALNE (do eksportu sesji):**
- ✅ `sg_timer_ble.py` (główny moduł)
- ✅ `export_sessions.py` (eksport do CSV)
- ✅ `run_export.py` (prosty launcher)

**OPCJONALNE (dodatkowe funkcje):**
- `project.py` (menu)
- `examples_advanced.py` (zaawansowane przykłady)
- `data_logger.py` (logger)
- `test_ble.py` (testy)

---

## 📱 METODA 1: Thonny IDE (NAJŁATWIEJSZA - POLECANA)

### Krok 1: Zainstaluj Thonny

1. Wejdź na https://thonny.org/
2. Pobierz installer dla Windows
3. Zainstaluj (Next → Next → Install)
4. Uruchom Thonny

### Krok 2: Podłącz Pico

1. Weź kabel USB
2. Podłącz Pico do komputera
3. Poczekaj chwilę (system wykryje urządzenie)

### Krok 3: Ustaw interpreter

1. W Thonny, w **prawym dolnym rogu** zobaczysz aktualny interpreter
2. Kliknij na niego
3. Wybierz: **MicroPython (Raspberry Pi Pico)**
4. Wybierz właściwy port (np. `COM3` lub `/dev/ttyACM0`)
5. Kliknij **OK**

Jeśli widzisz **`>>>`** w dolnym panelu - działa! 🎉

### Krok 4: Otwórz panel plików

1. W menu: **View** → **Files** (lub naciśnij Alt+V, F)
2. Zobaczysz dwa panele:
   - **Górny** = Pliki na Twoim komputerze
   - **Dolny** = Pliki na Pico

### Krok 5: Znajdź pliki projektu

W górnym panelu przejdź do folderu:
```
C:\Users\jaroslaw.zjawinski\OneDrive - Zjawa.IT - Jarosław Zjawiński\GIT\www.timer.pifpaf.fun\hardware\timer.pifpaf.fun\
```

### Krok 6: Prześlij pliki (WAŻNE!)

**Dla każdego pliku:**

1. **Otwórz plik** w Thonny:
   - File → Open → wybierz plik (np. `sg_timer_ble.py`)

2. **Zapisz na Pico:**
   - File → Save as... (lub Ctrl+Shift+S)
   - Wybierz **Raspberry Pi Pico**
   - Wpisz tę samą nazwę: `sg_timer_ble.py`
   - Kliknij **OK**

3. **Powtórz dla pozostałych plików:**
   - `export_sessions.py` → Zapisz jako `export_sessions.py`
   - `run_export.py` → Zapisz jako `run_export.py`

**UWAGA:** Nazwa pliku na Pico MUSI być identyczna jak na komputerze!

### Krok 7: Weryfikacja

W dolnym panelu (pliki na Pico) powinieneś zobaczyć:
```
/
├── sg_timer_ble.py
├── export_sessions.py
├── run_export.py
└── ... (inne pliki)
```

Lub w konsoli REPL wpisz:
```python
import os
print(os.listdir('/'))
```

Powinieneś zobaczyć listę z Twoimi plikami!

### Krok 8: Gotowe!

Teraz możesz uruchomić eksport:
```python
import run_export
```

---

## 💻 METODA 2: mpremote (dla zaawansowanych)

### Krok 1: Instalacja

Otwórz **Command Prompt** (cmd) lub **PowerShell** i wykonaj:

```bash
pip install mpremote
```

### Krok 2: Znajdź port Pico

```bash
mpremote connect list
```

Zobaczysz coś jak:
```
COM3 e660... Raspberry Pi Pico 2 W
```

Twój port to **COM3** (może być inny numer).

### Krok 3: Przejdź do folderu z plikami

```bash
cd "C:\Users\jaroslaw.zjawinski\OneDrive - Zjawa.IT - Jarosław Zjawiński\GIT\www.timer.pifpaf.fun\hardware\timer.pifpaf.fun"
```

### Krok 4: Prześlij pliki

**Jeden po drugim:**
```bash
mpremote connect COM3 fs cp sg_timer_ble.py :sg_timer_ble.py
mpremote connect COM3 fs cp export_sessions.py :export_sessions.py
mpremote connect COM3 fs cp run_export.py :run_export.py
```

**UWAGA:** Zamień `COM3` na Twój port!

**Format:** `mpremote connect PORT fs cp plik_źródłowy :plik_docelowy`

### Krok 5: Weryfikacja

```bash
mpremote connect COM3 fs ls
```

Powinieneś zobaczyć listę przesłanych plików.

### Krok 6: Uruchom eksport

```bash
mpremote connect COM3 exec "import run_export"
```

---

## 🔧 METODA 3: ampy (alternatywa)

### Krok 1: Instalacja

```bash
pip install adafruit-ampy
```

### Krok 2: Przejdź do folderu

```bash
cd "C:\Users\jaroslaw.zjawinski\OneDrive - Zjawa.IT - Jarosław Zjawiński\GIT\www.timer.pifpaf.fun\hardware\timer.pifpaf.fun"
```

### Krok 3: Prześlij pliki

```bash
ampy --port COM3 put sg_timer_ble.py
ampy --port COM3 put export_sessions.py
ampy --port COM3 put run_export.py
```

**UWAGA:** Zamień `COM3` na Twój port!

### Krok 4: Weryfikacja

```bash
ampy --port COM3 ls
```

### Krok 5: Uruchom (przez REPL)

```bash
ampy --port COM3 run run_export.py
```

Lub podłącz się przez terminal szeregowy (PuTTY, screen, etc.) i wpisz:
```python
import run_export
```

---

## 📂 METODA 4: Ręczne kopiowanie (tryb USB Mass Storage)

**UWAGA:** Ta metoda NIE działa jeśli na Pico jest już MicroPython! Musisz być w trybie BOOTSEL.

### Dla nowej instalacji:

1. **Odłącz** Pico od USB
2. **Przytrzymaj przycisk BOOTSEL** na Pico
3. **Podłącz** Pico do USB (wciąż trzymając BOOTSEL)
4. **Zwolnij** BOOTSEL
5. Pico pojawi się jako dysk **RPI-RP2**
6. Skopiuj pliki `.py` na ten dysk
7. **Odłącz i podłącz** Pico ponownie

**PROBLEM:** Pliki `.py` NIE będą działać bez MicroPython! Ta metoda jest tylko do wgrywania firmware `.uf2`.

---

## ❓ Rozwiązywanie problemów

### 🔴 "Port COM3 not found"

**Rozwiązanie:**
1. Sprawdź w **Menedżerze Urządzeń** (Device Manager)
2. Sekcja: **Porty (COM i LPT)**
3. Szukaj: "USB Serial Device" lub "Pico"
4. Numer portu będzie w nawiasie: **(COM5)**

### 🔴 "Permission denied" lub "Access denied"

**Rozwiązanie:**
1. Zamknij wszystkie programy używające portu (inne Thonny, PuTTY, Arduino IDE)
2. Odłącz i podłącz ponownie Pico
3. Spróbuj ponownie

### 🔴 "File not found" podczas przesyłania

**Rozwiązanie:**
1. Sprawdź czy jesteś w odpowiednim folderze:
   ```bash
   cd "C:\Users\jaroslaw.zjawinski\OneDrive - Zjawa.IT - Jarosław Zjawiński\GIT\www.timer.pifpaf.fun\hardware\timer.pifpaf.fun"
   ```
2. Sprawdź czy plik istnieje:
   ```bash
   dir sg_timer_ble.py
   ```

### 🔴 "Thonny nie widzi Pico"

**Rozwiązanie:**
1. Sprawdź czy kabel USB to kabel **DATA** (nie tylko do ładowania)
2. Spróbuj innego portu USB na komputerze
3. Odłącz inne urządzenia USB
4. Zrestartuj Thonny
5. Zainstaluj sterowniki USB: https://www.raspberrypi.com/documentation/microcontrollers/raspberry-pi-pico.html

### 🔴 "No module named 'micropython'" w cmd

**To normalne!** Komendy `mpremote` i `ampy` działają na komputerze, nie w Pythonie.

Uruchamiaj je bezpośrednio w **Command Prompt** lub **PowerShell**, nie w Python REPL!

---

## ✅ Jak sprawdzić czy pliki są na Pico?

### W Thonny:
- View → Files
- Sprawdź dolny panel (Raspberry Pi Pico)

### W REPL (konsola):
```python
import os
files = os.listdir('/')
print("Pliki na Pico:")
for f in files:
    print(f"  - {f}")
```

### Przez mpremote:
```bash
mpremote connect COM3 fs ls
```

### Przez ampy:
```bash
ampy --port COM3 ls
```

---

## 🎓 Praktyczny przykład krok po kroku (Thonny)

```
1. Uruchom Thonny
   └─> Podłącz Pico przez USB
       └─> Prawy dolny róg → MicroPython (Raspberry Pi Pico)
           └─> View → Files
               └─> File → Open → sg_timer_ble.py
                   └─> File → Save as → Raspberry Pi Pico → sg_timer_ble.py
                       └─> Powtórz dla export_sessions.py
                           └─> Powtórz dla run_export.py
                               └─> W konsoli wpisz: import run_export
                                   └─> GOTOWE! 🎉
```

---

## 🚀 Najszybsza metoda (dla znających terminal)

Jedna komenda, wszystkie pliki:

```bash
cd "C:\Users\jaroslaw.zjawinski\OneDrive - Zjawa.IT - Jarosław Zjawiński\GIT\www.timer.pifpaf.fun\hardware\timer.pifpaf.fun" && mpremote connect COM3 fs cp sg_timer_ble.py : && mpremote connect COM3 fs cp export_sessions.py : && mpremote connect COM3 fs cp run_export.py : && echo GOTOWE
```

(Zamień COM3 na Twój port)

---

## 📋 Checklist

Przed uruchomieniem sprawdź:

- [ ] Pico jest podłączone przez USB
- [ ] Thonny widzi Pico (MicroPython w prawym dolnym rogu)
- [ ] Pliki są na Pico (View → Files)
- [ ] Pliki mają poprawne nazwy (bez spacji, z rozszerzeniem .py)
- [ ] W konsoli widzisz `>>>` (REPL działa)

Jeśli wszystko ✅ to możesz uruchomić:
```python
import run_export
```

---

**Powodzenia! Jeśli masz problemy, sprawdź sekcję "Rozwiązywanie problemów" powyżej.** 🎯
