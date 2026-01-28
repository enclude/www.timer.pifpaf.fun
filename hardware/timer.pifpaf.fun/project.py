"""
SG Timer BLE Reader - Główny plik projektu
Prosty interfejs do wyboru akcji
"""

import asyncio


def show_menu():
    """Wyświetla menu główne"""
    print("\n" + "="*60)
    print("SG TIMER BLE READER")
    print("="*60)
    print("\nWybierz akcję:")
    print("")
    print("1. Eksportuj wszystkie sesje do CSV (POLECANE)")
    print("2. Odczytaj i wyświetl wszystkie dane")
    print("3. Nasłuchuj eventów na żywo")
    print("4. Test połączenia BLE")
    print("")
    print("0. Wyjście")
    print("")


async def option_1_export_sessions():
    """Opcja 1: Eksport sesji"""
    from export_sessions import export_all_sessions_to_csv
    await export_all_sessions_to_csv()


async def option_2_read_all():
    """Opcja 2: Odczyt wszystkich danych"""
    from sg_timer_ble import SGTimerBLE

    timer = SGTimerBLE()

    if await timer.scan_and_connect():
        await timer.discover_services()
        await timer.read_all_data()
        await timer.disconnect()


async def option_3_live_events():
    """Opcja 3: Nasłuchiwanie eventów"""
    from sg_timer_ble import SGTimerBLE

    timer = SGTimerBLE()

    if await timer.scan_and_connect():
        await timer.discover_services()
        print("\n📡 Nasłuchiwanie eventów (Ctrl+C aby zatrzymać)...")
        print("    Uruchom sesję na timerze aby zobaczyć eventy\n")

        try:
            await timer.subscribe_to_events()
        except KeyboardInterrupt:
            print("\nPrzerwano nasłuchiwanie")

        await timer.disconnect()


async def option_4_test_ble():
    """Opcja 4: Test BLE"""
    from test_ble import run_all_tests
    await run_all_tests()


async def main():
    """Główna funkcja z menu"""

    while True:
        show_menu()

        try:
            choice = input("Wybór: ").strip()
        except:
            # Dla środowisk bez input() (np. autostart)
            # Domyślnie eksportuj sesje
            choice = "1"

        print("")

        if choice == "1":
            await option_1_export_sessions()
            break

        elif choice == "2":
            await option_2_read_all()
            break

        elif choice == "3":
            await option_3_live_events()
            break

        elif choice == "4":
            await option_4_test_ble()
            break

        elif choice == "0":
            print("Zakończono")
            break

        else:
            print("❌ Nieprawidłowy wybór, spróbuj ponownie\n")


# Uruchom aplikację
if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\n\nPrzerwano przez użytkownika")
    except Exception as e:
        print(f"\n❌ Błąd: {e}")
        import sys
        sys.print_exception(e)
