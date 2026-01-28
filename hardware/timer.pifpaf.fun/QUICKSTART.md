# Quick Start - Eksport sesji z SG Timer

## 🚀 Szybkie uruchomienie

### Krok 1: Prześlij pliki na Pico

Musisz przesłać na Raspberry Pi Pico 2 W następujące pliki:
- `sg_timer_ble.py`
- `export_sessions.py`
- `project.py` (opcjonalnie - menu)

#### Przez Thonny IDE:
1. Otwórz plik w Thonny
2. **File** → **Save as...**
3. Wybierz **Raspberry Pi Pico**
4. Zapisz w katalogu głównym

#### Przez mpremote (z terminala):
```bash
mpremote connect COM3 fs cp sg_timer_ble.py :
mpremote connect COM3 fs cp export_sessions.py :
mpremote connect COM3 fs cp project.py :
```

### Krok 2: Uruchom eksport

#### Opcja A: Z menu (najprostsze)
```python
import project
```

Wybierz opcję **1** aby wyeksportować wszystkie sesje.

#### Opcja B: Bezpośrednio
```python
import asyncio
from export_sessions import export_all_sessions_to_csv

asyncio.run(export_all_sessions_to_csv())
```

#### Opcja C: Jedna linijka
```python
import asyncio; asyncio.run(__import__('export_sessions').export_all_sessions_to_csv())
```

## 📁 Co zostanie stworzone?

Po uruchomieniu eksportu na Pico zostaną utworzone pliki:

### 1. Pliki CSV dla każdej sesji
```
session_1737830000_2026-01-25_10-33-20.csv
session_1737820000_2026-01-25_07-46-40.csv
...
```

Format każdego pliku:
```csv
Shot_Number,Time_ms,Time_s,Split_ms,Split_s
0,1234,1.234,0,0.000
1,2456,2.456,1222,1.222
2,3678,3.678,1222,1.222
```

### 2. Plik podsumowujący
```
sessions_summary.csv
```

Format:
```csv
Session_ID,Date_Time,Total_Shots,Total_Time_s,First_Shot_s,Last_Shot_s,Avg_Split_s,Min_Split_s,Max_Split_s
1737830000,2026-01-25 10:33:20,5,12.345,1.234,12.345,2.468,2.100,2.800
```

## 💾 Jak pobrać pliki CSV z Pico?

### Metoda 1: Przez Thonny
1. **View** → **Files**
2. W dolnym panelu zobaczysz pliki na Pico
3. Kliknij prawym na plik CSV → **Download to...**
4. Wybierz lokalizację na komputerze

### Metoda 2: Przez mpremote
```bash
# Pobierz wszystkie CSV
mpremote connect COM3 fs cp :session_*.csv .
mpremote connect COM3 fs cp :sessions_summary.csv .
```

### Metoda 3: Przez ampy
```bash
# Lista plików na Pico
ampy --port COM3 ls

# Pobierz konkretny plik
ampy --port COM3 get session_1737830000_2026-01-25_10-33-20.csv
```

## 🔍 Weryfikacja

### Sprawdź czy pliki są na Pico:
```python
import os
files = [f for f in os.listdir('/') if f.endswith('.csv')]
print(f"Znaleziono {len(files)} plików CSV:")
for f in files:
    print(f"  - {f}")
```

### Podgląd zawartości pliku:
```python
with open('session_1737830000_2026-01-25_10-33-20.csv', 'r') as f:
    print(f.read())
```

## ⚠️ Rozwiązywanie problemów

### Błąd: "ImportError: no module named 'sg_timer_ble'"
**Rozwiązanie:** Upewnij się że plik `sg_timer_ble.py` jest na Pico:
```python
import os
print('sg_timer_ble.py' in os.listdir('/'))
```

### Błąd: "Nie można połączyć się z timerem"
**Rozwiązanie:**
1. Sprawdź czy timer jest włączony
2. Sprawdź czy BLE jest aktywne
3. Umieść timer bliżej Pico (< 10m)
4. Uruchom test: `import test_ble; asyncio.run(test_ble.quick_test())`

### Brak plików CSV po eksporcie
**Rozwiązanie:**
1. Sprawdź czy eksport się zakończył bez błędów
2. Sprawdź dostępną pamięć: `import os; os.statvfs('/')`
3. Zobacz logi w konsoli - może nie było żadnych sesji

### Pico się zawiesza podczas eksportu
**Rozwiązanie:**
1. Zrestartuj Pico (odłącz i podłącz USB)
2. Uruchom eksport ponownie
3. Jeśli problem się powtarza, zmniejsz liczbę sesji:
   - Edytuj `export_sessions.py`
   - Zmień `if len(sessions) > 100:` na `if len(sessions) > 10:`

## 📊 Otwieranie CSV w Excel/LibreOffice

Pliki CSV można otworzyć w:
- **Microsoft Excel** - podwójne kliknięcie lub **Dane** → **Z pliku tekstowego/CSV**
- **LibreOffice Calc** - **Plik** → **Otwórz** → wybierz CSV
- **Google Sheets** - **Plik** → **Importuj** → **Prześlij**
- **Python/Pandas** - `pd.read_csv('session_xyz.csv')`

## 🎯 Przykładowe wyjście

```
============================================================
EKSPORT WSZYSTKICH SESJI DO CSV
============================================================

[1/4] Łączenie z SG Timer...
Skanowanie urządzeń BLE...

Znaleziono urządzenie: SG-SST4A00123
Łączenie...
Połączono!

[2/4] Odkrywanie serwisów...
✓ COMMAND
✓ EVENT
✓ SAVED_SESSION_ID_LIST
✓ SHOT_LIST
✓ PAR_SETUP

[3/4] Odczytywanie listy sesji...
✓ Znaleziono 5 sesji

[4/4] Eksportowanie sesji do CSV...

  [1/5] Sesja 1737830000 (2026-01-25_10-33-20)... ✓ 8 strzałów → session_1737830000_2026-01-25_10-33-20.csv
  [2/5] Sesja 1737820000 (2026-01-25_07-46-40)... ✓ 6 strzałów → session_1737820000_2026-01-25_07-46-40.csv
  [3/5] Sesja 1737810000 (2026-01-25_05-00-00)... ✓ 10 strzałów → session_1737810000_2026-01-25_05-00-00.csv
  [4/5] Sesja 1737800000 (2026-01-25_02-13-20)... ✓ 5 strzałów → session_1737800000_2026-01-25_02-13-20.csv
  [5/5] Sesja 1737790000 (2026-01-24_23-26-40)... ✓ 12 strzałów → session_1737790000_2026-01-24_23-26-40.csv

[Bonus] Tworzenie pliku podsumowującego...
✓ Raport podsumowujący → sessions_summary.csv

============================================================
EKSPORT ZAKOŃCZONY
============================================================
✓ Wyeksportowano: 5 sesji
✓ Łącznie strzałów: 41
✓ Pliki CSV zapisane w bieżącym katalogu

Rozłączono
```

## 📞 Pomoc

Jeśli masz problemy:
1. Przeczytaj **[INSTALACJA.md](INSTALACJA.md)**
2. Uruchom test: `import test_ble; asyncio.run(test_ble.run_all_tests())`
3. Sprawdź logi błędów w konsoli
4. Zrestartuj Pico i Timer

---

**Gotowe! Twoje dane z SG Timer są teraz w plikach CSV! 🎉**
