"""
Test RO Session - Sprawdza czy sesje RO się zapisują
Prosty test bez zależności od examples_advanced
"""

import asyncio
import struct
from sg_timer_ble import SGTimerBLE


async def test_ro_session():
    """Test tworzenia sesji RO przez BLE"""

    print("\n" + "="*60)
    print("TEST: Czy sesje RO się zapisują w pamięci BLE?")
    print("="*60)

    timer = SGTimerBLE()

    try:
        # Połącz
        print("\n[1] Łączenie...")
        if not await timer.scan_and_connect():
            print("❌ Nie można połączyć")
            return

        print("[2] Odkrywanie serwisów...")
        if not await timer.discover_services():
            print("❌ Nie można odkryć serwisów")
            return

        if "COMMAND" not in timer.characteristics:
            print("❌ Brak charakterystyki COMMAND")
            return

        # KROK 1: Stan przed
        print("\n" + "="*60)
        print("KROK 1: Sprawdzam stan PRZED")
        print("="*60)

        sessions_before = await timer.read_saved_sessions()
        print(f"\n✓ Sesji w pamięci BLE: {len(sessions_before)}")

        # KROK 2: Utwórz sesję RO
        print("\n" + "="*60)
        print("KROK 2: Tworzę sesję RO przez BLE")
        print("="*60)

        print("\n📤 Wysyłam komendę SESSION_START (0x00)...")

        # Format komendy: [len][cmd_id]
        command = struct.pack('BB', 1, 0x00)  # len=1, cmd_id=0x00
        await timer.characteristics["COMMAND"].write(command)

        print("✓ Komenda wysłana")
        print("\n⏰ Czekam 10 sekund...")
        print("   💡 TIP: Możesz klaśnąć kilka razy aby symulować strzały")
        print("   (lub po prostu poczekaj - sesja i tak się zapisze)")

        await asyncio.sleep(10)

        # Zatrzymaj sesję
        print("\n📤 Wysyłam komendę SESSION_STOP (0x03)...")
        command = struct.pack('BB', 1, 0x03)  # len=1, cmd_id=0x03
        await timer.characteristics["COMMAND"].write(command)

        print("✓ Komenda wysłana")

        # KROK 3: Stan po
        print("\n" + "="*60)
        print("KROK 3: Czekam na zapis i sprawdzam stan PO")
        print("="*60)

        print("\n⏰ Czekam 2 sekundy na zapis...")
        await asyncio.sleep(2)

        print("\n🔍 Odczytuję listę sesji...")
        sessions_after = await timer.read_saved_sessions()
        print(f"\n✓ Sesji w pamięci BLE: {len(sessions_after)}")

        # ANALIZA
        print("\n" + "="*60)
        print("ANALIZA WYNIKU")
        print("="*60)

        new_sessions = len(sessions_after) - len(sessions_before)

        if new_sessions > 0:
            print(f"\n🎉 SUKCES! Dodano {new_sessions} nową sesję!")
            print("\n" + "="*60)
            print("📋 WNIOSKI:")
            print("="*60)
            print("✓ BLE API działa poprawnie")
            print("✓ Timer zapisuje sesje typu RO (Range Officer)")
            print("✓ Sesje tworzone przez BLE SĄ dostępne")

            print("\n❌ PROBLEM Z APLIKACJĄ MOBILNĄ:")
            print("✗ Stare sesje z aplikacji NIE są typu RO")
            print("✗ Aplikacja używa INNEGO protokołu/trybu")
            print("✗ Tylko sesje RO są dostępne przez BLE")

            print("\n💡 ROZWIĄZANIA:")
            print("1. Używaj BLE API do tworzenia nowych sesji")
            print("2. Sprawdź w aplikacji opcję 'RO Mode' lub 'Export to BLE'")
            print("3. Sprawdź menu timera - może być opcja zmiany trybu")
            print("4. Stare dane musisz wyeksportować przez aplikację/USB")

            # Pokaż nową sesję
            if sessions_after:
                new_session_id = sessions_after[0]
                from time import gmtime
                t = gmtime(new_session_id)
                date_str = f"{t[0]}-{t[1]:02d}-{t[2]:02d} {t[3]:02d}:{t[4]:02d}:{t[5]:02d}"

                print(f"\n📅 Nowa sesja RO:")
                print(f"   ID: {new_session_id}")
                print(f"   Data: {date_str} UTC")

                # Odczytaj strzały
                shots = await timer.read_shots_for_session(new_session_id)
                if shots:
                    print(f"   Strzałów: {len(shots)}")
                    print("\n   Szczegóły:")
                    for num, time_ms in shots[:10]:  # Max 10
                        print(f"     Strzał #{num}: {time_ms}ms ({time_ms/1000:.3f}s)")
                    if len(shots) > 10:
                        print(f"     ... i {len(shots)-10} więcej")
                else:
                    print("   Brak strzałów (sesja pusta)")

        elif new_sessions == 0:
            print("\n⚠️  Sesja NIE została zapisana!")
            print("\n" + "="*60)
            print("📋 MOŻLIWE PRZYCZYNY:")
            print("="*60)
            print("1. Timer GO może NIE zapisywać sesji w pamięci BLE")
            print("2. Wymaga specjalnej konfiguracji w menu timera")
            print("3. Firmware może mieć ograniczenie")
            print("4. Pamięć BLE może być pełna")

            print("\n💡 CO SPRAWDZIĆ:")
            print("• W menu timera szukaj:")
            print("  - 'BLE Mode' lub 'RO Mode'")
            print("  - 'Save to BLE' lub 'BLE Storage'")
            print("  - 'Memory Settings'")
            print("• Zrestartuj timer (wyłącz/włącz)")
            print("• Sprawdź wersję firmware w aplikacji")

        else:
            print("\n❓ Dziwny wynik - liczba sesji się zmniejszyła?")

    except Exception as e:
        print(f"\n❌ Błąd: {e}")
        import sys
        sys.print_exception(e)

    finally:
        await timer.disconnect()
        print("\n" + "="*60)
        print("Rozłączono")


# Uruchom test
if __name__ == "__main__":
    asyncio.run(test_ro_session())
