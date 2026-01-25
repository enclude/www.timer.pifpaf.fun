"""
Test BLE Connectivity - prosty skrypt testowy
Sprawdza czy Pico 2 W może skanować i łączyć się z urządzeniami BLE
"""

import asyncio
import aioble
import bluetooth


async def test_ble_basic():
    """Test 1: Podstawowa funkcjonalność BLE"""
    print("\n" + "="*60)
    print("TEST 1: Podstawowa funkcjonalność BLE")
    print("="*60)

    try:
        # Test inicjalizacji BLE
        print("\n✓ Moduł aioble zaimportowany")
        print("✓ Moduł bluetooth zaimportowany")

        # Test czy BLE jest aktywne
        ble = bluetooth.BLE()
        ble.active(True)
        print("✓ BLE aktywowany")

        print("\n✅ Test podstawowy PASSED")
        return True

    except Exception as e:
        print(f"\n❌ Test podstawowy FAILED: {e}")
        return False


async def test_ble_scan():
    """Test 2: Skanowanie urządzeń BLE"""
    print("\n" + "="*60)
    print("TEST 2: Skanowanie urządzeń BLE")
    print("="*60)

    try:
        print("\nSkanowanie przez 5 sekund...")
        devices_found = 0

        async with aioble.scan(5000, interval_us=30000, window_us=30000, active=True) as scanner:
            async for result in scanner:
                devices_found += 1
                name = result.name() if result.name() else "(bez nazwy)"
                print(f"  [{devices_found}] {name} | RSSI: {result.rssi} dBm | Adres: {result.device}")

                # Limit wyświetlania
                if devices_found >= 10:
                    print("  ... (ograniczono do 10 urządzeń)")
                    break

        if devices_found > 0:
            print(f"\n✅ Znaleziono {devices_found} urządzeń BLE")
            return True
        else:
            print("\n⚠️  Nie znaleziono żadnych urządzeń BLE")
            print("    Sprawdź czy w pobliżu są włączone urządzenia BLE")
            return False

    except Exception as e:
        print(f"\n❌ Test skanowania FAILED: {e}")
        return False


async def test_sg_timer_detection():
    """Test 3: Detekcja urządzenia SG Timer"""
    print("\n" + "="*60)
    print("TEST 3: Detekcja urządzenia SG Timer")
    print("="*60)

    try:
        print("\nSzukanie urządzenia SG-SST4*...")

        async with aioble.scan(10000, interval_us=30000, window_us=30000, active=True) as scanner:
            async for result in scanner:
                if result.name() and result.name().startswith("SG-SST4"):
                    print(f"\n✅ Znaleziono SG Timer!")
                    print(f"   Nazwa: {result.name()}")
                    print(f"   RSSI: {result.rssi} dBm")
                    print(f"   Adres: {result.device}")

                    # Próba odczytu UUID serwisu z advertisement data (jeśli dostępne)
                    return True

        print("\n⚠️  Nie znaleziono urządzenia SG Timer")
        print("    Sprawdź czy:")
        print("    - Timer jest włączony")
        print("    - BLE jest aktywne na timerze")
        print("    - Timer jest w zasięgu (< 10m)")
        return False

    except Exception as e:
        print(f"\n❌ Test detekcji FAILED: {e}")
        return False


async def test_sg_timer_connection():
    """Test 4: Połączenie z SG Timer"""
    print("\n" + "="*60)
    print("TEST 4: Połączenie z urządzeniem SG Timer")
    print("="*60)

    try:
        print("\nSzukanie i łączenie z SG Timer...")

        async with aioble.scan(10000, interval_us=30000, window_us=30000, active=True) as scanner:
            async for result in scanner:
                if result.name() and result.name().startswith("SG-SST4"):
                    print(f"\nZnaleziono: {result.name()}")
                    print("Łączenie...")

                    try:
                        connection = await result.device.connect(timeout_ms=5000)
                        print("✅ Połączono pomyślnie!")

                        # Rozłącz
                        await connection.disconnect()
                        print("✅ Rozłączono pomyślnie")

                        return True

                    except asyncio.TimeoutError:
                        print("❌ Timeout podczas łączenia")
                        return False
                    except Exception as conn_error:
                        print(f"❌ Błąd połączenia: {conn_error}")
                        return False

        print("\n⚠️  Nie znaleziono urządzenia SG Timer do połączenia")
        return False

    except Exception as e:
        print(f"\n❌ Test połączenia FAILED: {e}")
        return False


async def test_sg_timer_services():
    """Test 5: Odkrywanie serwisów SG Timer"""
    print("\n" + "="*60)
    print("TEST 5: Odkrywanie serwisów i charakterystyk")
    print("="*60)

    SERVICE_UUID = bluetooth.UUID("7520FFFF-14D2-4CDA-8B6B-697C554C9311")

    try:
        print("\nSzukanie urządzenia...")

        async with aioble.scan(10000, interval_us=30000, window_us=30000, active=True) as scanner:
            async for result in scanner:
                if result.name() and result.name().startswith("SG-SST4"):
                    print(f"\nZnaleziono: {result.name()}")
                    print("Łączenie...")

                    connection = await result.device.connect(timeout_ms=5000)
                    print("✅ Połączono")

                    try:
                        print("\nOdkrywanie serwisów...")
                        service = await connection.service(SERVICE_UUID)
                        print(f"✅ Znaleziono serwis główny: {SERVICE_UUID}")

                        # Lista charakterystyk do sprawdzenia
                        characteristics = {
                            "API_VERSION": bluetooth.UUID("7520FFFE-14D2-4CDA-8B6B-697C554C9311"),
                            "UNIX_TIME": bluetooth.UUID("75200006-14D2-4CDA-8B6B-697C554C9311"),
                            "PAR_SETUP": bluetooth.UUID("75200005-14D2-4CDA-8B6B-697C554C9311"),
                        }

                        print("\nSprawdzanie charakterystyk:")
                        for name, uuid in characteristics.items():
                            try:
                                char = await service.characteristic(uuid)
                                print(f"  ✓ {name}")
                            except:
                                print(f"  ✗ {name}")

                        await connection.disconnect()
                        print("\n✅ Test serwisów zakończony pomyślnie")
                        return True

                    except Exception as service_error:
                        print(f"❌ Błąd odkrywania serwisów: {service_error}")
                        await connection.disconnect()
                        return False

        print("\n⚠️  Nie znaleziono urządzenia SG Timer")
        return False

    except Exception as e:
        print(f"\n❌ Test serwisów FAILED: {e}")
        return False


async def run_all_tests():
    """Uruchamia wszystkie testy"""
    print("\n" + "="*60)
    print("🔬 SG TIMER BLE - DIAGNOSTYKA")
    print("="*60)
    print("\nUruchamianie testów diagnostycznych...")

    results = []

    # Test 1: Podstawy
    results.append(("Test podstawowy BLE", await test_ble_basic()))
    await asyncio.sleep(1)

    # Test 2: Skanowanie
    results.append(("Test skanowania BLE", await test_ble_scan()))
    await asyncio.sleep(1)

    # Test 3: Detekcja SG Timer
    results.append(("Detekcja SG Timer", await test_sg_timer_detection()))
    await asyncio.sleep(1)

    # Test 4: Połączenie
    results.append(("Połączenie z SG Timer", await test_sg_timer_connection()))
    await asyncio.sleep(1)

    # Test 5: Serwisy
    results.append(("Odkrywanie serwisów", await test_sg_timer_services()))

    # Podsumowanie
    print("\n" + "="*60)
    print("📊 PODSUMOWANIE TESTÓW")
    print("="*60)

    passed = 0
    failed = 0

    for test_name, result in results:
        status = "✅ PASSED" if result else "❌ FAILED"
        print(f"{status} - {test_name}")

        if result:
            passed += 1
        else:
            failed += 1

    print(f"\nWynik: {passed}/{len(results)} testów zakończonych sukcesem")

    if failed == 0:
        print("\n🎉 Wszystkie testy przeszły pomyślnie!")
        print("   Możesz uruchomić aplikację główną.")
    else:
        print(f"\n⚠️  {failed} testów nie powiodło się")
        print("   Sprawdź komunikaty błędów powyżej")

        if results[0][1] == False:
            print("\n💡 Sugestia: Problem z podstawową funkcjonalnością BLE")
            print("   - Sprawdź czy używasz Raspberry Pi Pico 2 W")
            print("   - Zaktualizuj firmware MicroPython")

        elif results[1][1] == False:
            print("\n💡 Sugestia: Problem ze skanowaniem BLE")
            print("   - Sprawdź czy w pobliżu są jakiekolwiek urządzenia BLE")
            print("   - Spróbuj zrestartować Pico")

        elif results[2][1] == False:
            print("\n💡 Sugestia: Nie można znaleźć SG Timer")
            print("   - Sprawdź czy timer jest włączony")
            print("   - Sprawdź czy BLE jest aktywne na timerze")
            print("   - Umieść timer bliżej Pico (< 5m)")


async def quick_test():
    """Szybki test - tylko podstawy"""
    print("\n🔬 Szybki test BLE...")

    # Test 1
    if not await test_ble_basic():
        return

    # Test 2
    if not await test_ble_scan():
        return

    print("\n✅ Podstawowe testy OK!")


async def main():
    """Menu główne"""
    print("\n" + "="*60)
    print("SG TIMER BLE - TESTER DIAGNOSTYCZNY")
    print("="*60)
    print("\nWybierz opcję:")
    print("1. Uruchom wszystkie testy (zalecane)")
    print("2. Szybki test (tylko podstawy)")
    print("3. Test skanowania BLE")
    print("4. Test detekcji SG Timer")
    print("5. Test połączenia z SG Timer")
    print("0. Wyjście")

    choice = input("\nWybór: ")

    if choice == "1":
        await run_all_tests()
    elif choice == "2":
        await quick_test()
    elif choice == "3":
        await test_ble_scan()
    elif choice == "4":
        await test_sg_timer_detection()
    elif choice == "5":
        await test_sg_timer_connection()
    elif choice == "0":
        print("Zakończono")
    else:
        print("Nieprawidłowy wybór")


if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\n\nPrzerwano przez użytkownika")
    except Exception as e:
        print(f"\n❌ Błąd krytyczny: {e}")
