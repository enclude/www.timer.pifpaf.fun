"""
Zaawansowane przykłady użycia SG Timer BLE
Kontrola sesji, wysyłanie komend, ustawianie parametrów
"""

import asyncio
import struct
from sg_timer_ble import SGTimerBLE, CMD_SESSION_START, CMD_SESSION_STOP, CMD_SESSION_SUSPEND, CMD_SESSION_RESUME


class SGTimerController(SGTimerBLE):
    """Rozszerzona klasa z funkcjami kontrolnymi"""

    async def send_command(self, cmd_id, cmd_data=b''):
        """Wysyła komendę do urządzenia"""
        if "COMMAND" not in self.characteristics:
            print("Charakterystyka COMMAND niedostępna")
            return False

        try:
            # Format: [len][cmd_id][cmd_data]
            length = 1 + len(cmd_data)  # cmd_id + cmd_data
            packet = struct.pack('BB', length, cmd_id) + cmd_data

            print(f"📤 Wysyłanie komendy: 0x{cmd_id:02X}")
            await self.characteristics["COMMAND"].write(packet)

            # Czekaj na odpowiedź przez notyfikację
            await asyncio.sleep(0.2)
            return True

        except Exception as e:
            print(f"Błąd wysyłania komendy: {e}")
            return False

    async def start_session(self):
        """Rozpoczyna sesję RO"""
        print("\n🎬 Rozpoczynanie sesji...")
        return await self.send_command(CMD_SESSION_START)

    async def stop_session(self):
        """Zatrzymuje sesję RO"""
        print("\n⏹️  Zatrzymywanie sesji...")
        return await self.send_command(CMD_SESSION_STOP)

    async def suspend_session(self):
        """Wstrzymuje sesję RO"""
        print("\n⏸️  Wstrzymywanie sesji...")
        return await self.send_command(CMD_SESSION_SUSPEND)

    async def resume_session(self):
        """Wznawia sesję RO"""
        print("\n▶️  Wznawianie sesji...")
        return await self.send_command(CMD_SESSION_RESUME)

    async def set_par_setup(self, start_delay=30, time_limit=0, shot_limit=0):
        """
        Ustawia konfigurację PAR

        Args:
            start_delay: opóźnienie startu w jednostkach 0.1s (30 = 3.0s)
                        0xFFFF dla losowego opóźnienia 1-4s
            time_limit: limit czasu w jednostkach 0.1s (0 = bez limitu)
            shot_limit: limit strzałów (0 = bez limitu)
        """
        if "PAR_SETUP" not in self.characteristics:
            print("Charakterystyka PAR_SETUP niedostępna")
            return False

        try:
            data = struct.pack('>HHH', start_delay, time_limit, shot_limit)
            await self.characteristics["PAR_SETUP"].write(data)

            print("\n⚙️  Ustawiono konfigurację PAR:")
            if start_delay == 0xFFFF:
                print(f"   Start Delay: Losowy (1.0-4.0s)")
            else:
                print(f"   Start Delay: {start_delay * 0.1:.1f}s")
            print(f"   Time Limit: {time_limit * 0.1:.1f}s" if time_limit > 0 else "   Time Limit: Bez limitu")
            print(f"   Shot Limit: {shot_limit}" if shot_limit > 0 else "   Shot Limit: Bez limitu")

            return True
        except Exception as e:
            print(f"Błąd ustawiania PAR_SETUP: {e}")
            return False

    async def set_unix_time(self, unix_time=None):
        """
        Ustawia czas Unix w urządzeniu

        Args:
            unix_time: timestamp Unix (None = użyj aktualnego czasu)
        """
        if "UNIX_TIME" not in self.characteristics:
            print("Charakterystyka UNIX_TIME niedostępna")
            return False

        try:
            if unix_time is None:
                # Użyj aktualnego czasu (wymaga ustawionego RTC)
                import time
                unix_time = int(time.time())

            data = struct.pack('>I', unix_time)
            await self.characteristics["UNIX_TIME"].write(data)

            from time import gmtime
            t = gmtime(unix_time)
            print(f"\n🕐 Ustawiono czas: {t[0]}-{t[1]:02d}-{t[2]:02d} {t[3]:02d}:{t[4]:02d}:{t[5]:02d} UTC")
            return True
        except Exception as e:
            print(f"Błąd ustawiania UNIX_TIME: {e}")
            return False

    async def subscribe_to_command_responses(self):
        """Subskrybuje odpowiedzi na komendy"""
        if "COMMAND" not in self.characteristics:
            print("Charakterystyka COMMAND niedostępna")
            return

        print("\n📡 Nasłuchiwanie odpowiedzi na komendy...")

        try:
            await self.characteristics["COMMAND"].subscribe(notify=True)

            while True:
                data = await self.characteristics["COMMAND"].notified()
                if len(data) >= 3:
                    length = data[0]
                    cmd_id = data[1]
                    resp_code = data[2]

                    status = "✅ SUCCESS" if resp_code == 0x00 else "❌ ERROR"
                    print(f"\n📨 Odpowiedź na komendę 0x{cmd_id:02X}: {status}")

        except asyncio.CancelledError:
            print("\nZatrzymano nasłuchiwanie")
        except Exception as e:
            print(f"Błąd subskrypcji: {e}")

    async def live_session_monitor(self, duration_seconds=60):
        """
        Monitoruje sesję w czasie rzeczywistym

        Args:
            duration_seconds: czas monitorowania w sekundach
        """
        if "EVENT" not in self.characteristics:
            print("Charakterystyka EVENT niedostępna")
            return

        print(f"\n📊 Monitorowanie sesji przez {duration_seconds}s...")
        print("=" * 50)

        shots = []
        session_id = None
        start_time = None

        try:
            await self.characteristics["EVENT"].subscribe(notify=True)

            async def timeout_handler():
                await asyncio.sleep(duration_seconds)

            async def event_handler():
                nonlocal shots, session_id, start_time

                while True:
                    data = await self.characteristics["EVENT"].notified()
                    if len(data) < 2:
                        continue

                    event_id = data[1]

                    if event_id == 0x00:  # SESSION_STARTED
                        session_id = struct.unpack('>I', data[2:6])[0]
                        start_delay = struct.unpack('>H', data[6:8])[0]
                        print(f"\n🎬 SESJA ROZPOCZĘTA")
                        print(f"   ID: {session_id}")
                        print(f"   Opóźnienie: {start_delay * 0.1:.1f}s")
                        shots = []

                    elif event_id == 0x05:  # SESSION_SET_BEGIN
                        print(f"\n🚦 START! (po opóźnieniu)")
                        import time
                        start_time = time.ticks_ms()

                    elif event_id == 0x04:  # SHOT_DETECTED
                        shot_num = struct.unpack('>H', data[6:8])[0]
                        shot_time = struct.unpack('>I', data[8:12])[0]
                        shots.append((shot_num, shot_time))

                        # Oblicz split (różnicę od poprzedniego strzału)
                        if len(shots) > 1:
                            split = shot_time - shots[-2][1]
                            print(f"🎯 Strzał #{shot_num}: {shot_time/1000:.3f}s (split: {split/1000:.3f}s)")
                        else:
                            print(f"🎯 Strzał #{shot_num}: {shot_time/1000:.3f}s")

                    elif event_id == 0x03:  # SESSION_STOPPED
                        total_shots = struct.unpack('>H', data[6:8])[0]
                        print(f"\n⏹️  SESJA ZAKOŃCZONA")
                        print(f"   Łącznie strzałów: {total_shots}")
                        if shots:
                            avg_split = (shots[-1][1] / len(shots)) / 1000
                            print(f"   Średni split: {avg_split:.3f}s")

            # Uruchom oba handlery równolegle
            await asyncio.gather(
                event_handler(),
                timeout_handler()
            )

        except asyncio.CancelledError:
            pass
        except Exception as e:
            print(f"Błąd monitorowania: {e}")

        print("\n" + "=" * 50)
        print(f"Zakończono monitorowanie")


# ============================================================================
# PRZYKŁADY UŻYCIA
# ============================================================================

async def example_1_basic_session_control():
    """Przykład 1: Podstawowa kontrola sesji"""
    print("\n" + "="*60)
    print("PRZYKŁAD 1: Kontrola sesji")
    print("="*60)

    timer = SGTimerController()

    try:
        if not await timer.scan_and_connect():
            return

        await timer.discover_services()

        # Rozpocznij sesję
        await timer.start_session()
        await asyncio.sleep(5)

        # Wstrzymaj
        await timer.suspend_session()
        await asyncio.sleep(2)

        # Wznów
        await timer.resume_session()
        await asyncio.sleep(3)

        # Zatrzymaj
        await timer.stop_session()

    finally:
        await timer.disconnect()


async def example_2_configure_par():
    """Przykład 2: Konfiguracja parametrów PAR"""
    print("\n" + "="*60)
    print("PRZYKŁAD 2: Konfiguracja PAR")
    print("="*60)

    timer = SGTimerController()

    try:
        if not await timer.scan_and_connect():
            return

        await timer.discover_services()

        # Odczytaj aktualną konfigurację
        print("\n📖 Aktualna konfiguracja:")
        await timer.read_par_setup()

        # Ustaw nową konfigurację
        # 3 sekundy opóźnienia, 60 sekund limitu, 5 strzałów
        await timer.set_par_setup(
            start_delay=30,      # 3.0s
            time_limit=600,      # 60.0s
            shot_limit=5
        )

        # Weryfikuj
        print("\n✅ Nowa konfiguracja:")
        await timer.read_par_setup()

    finally:
        await timer.disconnect()


async def example_3_live_monitoring():
    """Przykład 3: Monitoring sesji na żywo"""
    print("\n" + "="*60)
    print("PRZYKŁAD 3: Monitoring sesji na żywo")
    print("="*60)

    timer = SGTimerController()

    try:
        if not await timer.scan_and_connect():
            return

        await timer.discover_services()

        # Ustaw parametry
        await timer.set_par_setup(start_delay=30, time_limit=0, shot_limit=10)

        # Rozpocznij monitoring (w tle)
        monitor_task = asyncio.create_task(timer.live_session_monitor(duration_seconds=120))

        # Poczekaj chwilę i rozpocznij sesję
        await asyncio.sleep(2)
        await timer.start_session()

        # Czekaj na zakończenie monitoringu
        await monitor_task

    finally:
        await timer.disconnect()


async def example_4_set_time():
    """Przykład 4: Synchronizacja czasu"""
    print("\n" + "="*60)
    print("PRZYKŁAD 4: Synchronizacja czasu")
    print("="*60)

    timer = SGTimerController()

    try:
        if not await timer.scan_and_connect():
            return

        await timer.discover_services()

        # Odczytaj aktualny czas
        print("\n📖 Aktualny czas w urządzeniu:")
        await timer.read_unix_time()

        # Ustaw nowy czas (przykładowy timestamp)
        # W rzeczywistej aplikacji użyj aktualnego czasu z RTC lub NTP
        import time
        current_time = int(time.time())
        await timer.set_unix_time(current_time)

        # Weryfikuj
        print("\n✅ Czas po synchronizacji:")
        await timer.read_unix_time()

    finally:
        await timer.disconnect()


async def example_5_full_workflow():
    """Przykład 5: Pełny workflow - konfiguracja i sesja"""
    print("\n" + "="*60)
    print("PRZYKŁAD 5: Pełny workflow treningowy")
    print("="*60)

    timer = SGTimerController()

    try:
        if not await timer.scan_and_connect():
            return

        await timer.discover_services()

        # 1. Synchronizuj czas
        print("\n▶️  Krok 1: Synchronizacja czasu")
        import time
        await timer.set_unix_time(int(time.time()))

        # 2. Konfiguruj PAR
        print("\n▶️  Krok 2: Konfiguracja PAR")
        await timer.set_par_setup(
            start_delay=0xFFFF,  # Losowe opóźnienie
            time_limit=0,        # Bez limitu czasu
            shot_limit=6         # 6 strzałów
        )

        # 3. Rozpocznij monitoring
        print("\n▶️  Krok 3: Uruchamianie monitoringu")
        monitor_task = asyncio.create_task(timer.live_session_monitor(duration_seconds=180))

        await asyncio.sleep(1)

        # 4. Rozpocznij sesję
        print("\n▶️  Krok 4: Rozpoczęcie sesji")
        await timer.start_session()

        # 5. Czekaj na zakończenie
        await monitor_task

        # 6. Odczytaj zapisane dane
        print("\n▶️  Krok 5: Odczyt zapisanych danych")
        sessions = await timer.read_saved_sessions()
        if sessions:
            await timer.read_shots_for_session(sessions[0])

    finally:
        await timer.disconnect()


# ============================================================================
# GŁÓWNE MENU
# ============================================================================

async def main():
    """Menu główne z przykładami"""
    print("\n" + "="*60)
    print("SG TIMER BLE - Zaawansowane przykłady")
    print("="*60)
    print("\nWybierz przykład:")
    print("1. Podstawowa kontrola sesji (start/stop/suspend/resume)")
    print("2. Konfiguracja parametrów PAR")
    print("3. Monitoring sesji na żywo")
    print("4. Synchronizacja czasu")
    print("5. Pełny workflow treningowy")
    print("0. Wyjście")

    choice = input("\nWybór: ")

    if choice == "1":
        await example_1_basic_session_control()
    elif choice == "2":
        await example_2_configure_par()
    elif choice == "3":
        await example_3_live_monitoring()
    elif choice == "4":
        await example_4_set_time()
    elif choice == "5":
        await example_5_full_workflow()
    elif choice == "0":
        print("Zakończono")
    else:
        print("Nieprawidłowy wybór")


if __name__ == "__main__":
    asyncio.run(main())
