"""
Debug Sessions - Diagnostyka odczytu sesji
Pokazuje surowe dane z charakterystyki SAVED_SESSION_ID_LIST
"""

import asyncio
import struct
from sg_timer_ble import SGTimerBLE


async def debug_session_reading():
    """Szczegółowa diagnostyka odczytu sesji"""

    print("\n" + "="*60)
    print("DIAGNOSTYKA ODCZYTU SESJI")
    print("="*60)

    timer = SGTimerBLE()

    try:
        # Połącz
        print("\n[1] Łączenie z SG Timer...")
        if not await timer.scan_and_connect(timeout_ms=10000):
            print("❌ Nie można połączyć")
            return

        print("[2] Odkrywanie serwisów...")
        if not await timer.discover_services():
            print("❌ Nie można odkryć serwisów")
            return

        if "SAVED_SESSION_ID_LIST" not in timer.characteristics:
            print("❌ Brak charakterystyki SAVED_SESSION_ID_LIST")
            return

        print("✓ Charakterystyka SAVED_SESSION_ID_LIST dostępna\n")

        # Test 1: Spróbuj odczytać bez pisania
        print("="*60)
        print("TEST 1: Odczyt bez inicjalizacji")
        print("="*60)
        try:
            data = await timer.characteristics["SAVED_SESSION_ID_LIST"].read()
            sess_id = struct.unpack('>I', data)[0]
            print(f"Odczytano: 0x{sess_id:08X} ({sess_id})")

            if sess_id == 0xFFFFFFFF:
                print("→ Wartość 0xFFFFFFFF (pusty/koniec)")
            elif sess_id == 0x00000000:
                print("→ Wartość 0x00000000 (niezainicjowane)")
            else:
                print(f"→ To może być ID sesji! ({sess_id})")
        except Exception as e:
            print(f"❌ Błąd: {e}")

        # Test 2: Zapisz 0xFFFFFFFF (start od końca)
        print("\n" + "="*60)
        print("TEST 2: Inicjalizacja z 0xFFFFFFFF (ostatnia sesja)")
        print("="*60)
        try:
            print("Wysyłam: 0xFFFFFFFF...")
            await timer.characteristics["SAVED_SESSION_ID_LIST"].write(
                struct.pack('>I', 0xFFFFFFFF)
            )
            print("✓ Wysłano")

            await asyncio.sleep(0.1)  # Krótka pauza

            print("\nOdczytywanie sesji...")
            session_count = 0

            for i in range(20):  # Maksymalnie 20 prób
                data = await timer.characteristics["SAVED_SESSION_ID_LIST"].read()
                sess_id = struct.unpack('>I', data)[0]

                print(f"\n[{i+1}] Odczyt:")
                print(f"    Raw: {data.hex()}")
                print(f"    Hex: 0x{sess_id:08X}")
                print(f"    Dec: {sess_id}")

                if sess_id == 0xFFFFFFFF:
                    print("    → Koniec listy (0xFFFFFFFF)")
                    break
                elif sess_id == 0x00000000:
                    print("    → Wartość zerowa (0x00000000)")
                    break
                elif sess_id < 1000000000:  # Nieprawdopodobny timestamp
                    print(f"    ⚠️  Podejrzana wartość (za mała na timestamp Unix)")
                elif sess_id > 2000000000:  # Zbyt daleko w przyszłości
                    print(f"    ⚠️  Podejrzana wartość (za duża na timestamp Unix)")
                else:
                    # Konwertuj na datę
                    from time import gmtime
                    t = gmtime(sess_id)
                    date_str = f"{t[0]}-{t[1]:02d}-{t[2]:02d} {t[3]:02d}:{t[4]:02d}:{t[5]:02d}"
                    print(f"    ✓ ID Sesji: {sess_id}")
                    print(f"    📅 Data: {date_str} UTC")
                    session_count += 1

                await asyncio.sleep(0.1)

            print(f"\n{'='*60}")
            print(f"Znaleziono {session_count} sesji")
            print(f"{'='*60}")

        except Exception as e:
            print(f"❌ Błąd: {e}")
            import sys
            sys.print_exception(e)

        # Test 3: Zapisz 0x00000000 (start od początku)
        print("\n" + "="*60)
        print("TEST 3: Inicjalizacja z 0x00000000 (pierwsza sesja)")
        print("="*60)
        try:
            print("Wysyłam: 0x00000000...")
            await timer.characteristics["SAVED_SESSION_ID_LIST"].write(
                struct.pack('>I', 0x00000000)
            )
            print("✓ Wysłano")

            await asyncio.sleep(0.1)

            print("\nOdczytywanie sesji...")
            for i in range(5):
                data = await timer.characteristics["SAVED_SESSION_ID_LIST"].read()
                sess_id = struct.unpack('>I', data)[0]

                print(f"\n[{i+1}] Odczyt: 0x{sess_id:08X} ({sess_id})")

                if sess_id == 0xFFFFFFFF:
                    print("    → Koniec listy")
                    break
                elif sess_id > 0 and sess_id < 2000000000:
                    from time import gmtime
                    t = gmtime(sess_id)
                    date_str = f"{t[0]}-{t[1]:02d}-{t[2]:02d} {t[3]:02d}:{t[4]:02d}:{t[5]:02d}"
                    print(f"    ✓ Sesja: {date_str} UTC")

                await asyncio.sleep(0.1)

        except Exception as e:
            print(f"❌ Błąd: {e}")

        # Test 4: Sprawdź czy SHOT_LIST działa
        print("\n" + "="*60)
        print("TEST 4: Sprawdzenie charakterystyki SHOT_LIST")
        print("="*60)

        if "SHOT_LIST" not in timer.characteristics:
            print("❌ Brak charakterystyki SHOT_LIST")
        else:
            try:
                print("Próba odczytu bez inicjalizacji...")
                data = await timer.characteristics["SHOT_LIST"].read()
                shot_number, shot_time = struct.unpack('>HI', data)

                print(f"Odczytano:")
                print(f"  Shot Number: {shot_number}")
                print(f"  Shot Time: {shot_time} ms")

                if shot_time == 0xFFFFFFFF:
                    print("  → Brak strzałów / koniec listy")
                else:
                    print(f"  ✓ To może być strzał!")

            except Exception as e:
                print(f"❌ Błąd: {e}")

        # Test 5: Odczyt pozostałych charakterystyk
        print("\n" + "="*60)
        print("TEST 5: Inne charakterystyki")
        print("="*60)

        # RESERVED
        if "RESERVED" in timer.characteristics:
            try:
                print("\nRESERVED:")
                data = await timer.characteristics["RESERVED"].read()
                print(f"  Raw: {data.hex()}")
                print(f"  Długość: {len(data)} bajtów")
            except Exception as e:
                print(f"  Błąd: {e}")

        print("\n" + "="*60)
        print("DIAGNOSTYKA ZAKOŃCZONA")
        print("="*60)
        print("\n💡 Analiza:")
        print("   Jeśli widzisz sesje w TEST 2 - skrypt działa poprawnie")
        print("   Jeśli wszędzie 0xFFFFFFFF - timer może być pusty lub w złym stanie")
        print("   Jeśli błędy odczytu - może być problem z BLE")

    except Exception as e:
        print(f"\n❌ Błąd główny: {e}")
        import sys
        sys.print_exception(e)

    finally:
        await timer.disconnect()
        print("\nRozłączono")


async def try_alternative_read():
    """Alternatywna metoda odczytu - bez początkowego write"""

    print("\n" + "="*60)
    print("ALTERNATYWNA METODA ODCZYTU")
    print("="*60)

    timer = SGTimerBLE()

    try:
        if not await timer.scan_and_connect():
            return

        if not await timer.discover_services():
            return

        if "SAVED_SESSION_ID_LIST" not in timer.characteristics:
            print("❌ Brak charakterystyki")
            return

        print("\nCzytam bezpośrednio (bez inicjalizacji)...")
        print("Może timer pamiętać ostatnią pozycję?\n")

        sessions = []
        for i in range(50):
            data = await timer.characteristics["SAVED_SESSION_ID_LIST"].read()
            sess_id = struct.unpack('>I', data)[0]

            print(f"[{i+1}] 0x{sess_id:08X}", end="")

            if sess_id == 0xFFFFFFFF:
                print(" → Koniec")
                break
            elif sess_id > 1000000000 and sess_id < 2000000000:
                from time import gmtime
                t = gmtime(sess_id)
                date_str = f"{t[0]}-{t[1]:02d}-{t[2]:02d} {t[3]:02d}:{t[4]:02d}"
                print(f" ✓ Sesja {date_str}")
                sessions.append(sess_id)
            else:
                print(" ? Nieznana wartość")

            await asyncio.sleep(0.1)

        print(f"\nZnaleziono {len(sessions)} sesji")

    finally:
        await timer.disconnect()


async def main():
    """Menu główne"""
    print("\n" + "="*60)
    print("DIAGNOSTYKA SESJI SG TIMER")
    print("="*60)
    print("\nWybierz test:")
    print("1. Pełna diagnostyka (polecana)")
    print("2. Alternatywna metoda odczytu")
    print("3. Oba testy")
    print("")

    try:
        choice = input("Wybór: ").strip()
    except:
        choice = "1"

    if choice == "1":
        await debug_session_reading()
    elif choice == "2":
        await try_alternative_read()
    elif choice == "3":
        await debug_session_reading()
        await asyncio.sleep(2)
        await try_alternative_read()
    else:
        print("Nieprawidłowy wybór")


if __name__ == "__main__":
    asyncio.run(main())
