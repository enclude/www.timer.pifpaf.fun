"""
Smart Shot Timer BLE Reader dla Raspberry Pi Pico 2 W
Zbiera wszystkie dane z urządzenia SG Timer przez BLE
"""

import asyncio
import aioble
import bluetooth
import struct
from micropython import const

# UUID serwisu głównego
SERVICE_UUID = bluetooth.UUID("7520FFFF-14D2-4CDA-8B6B-697C554C9311")

# UUID charakterystyk
CHAR_COMMAND = bluetooth.UUID("75200000-14D2-4CDA-8B6B-697C554C9311")
CHAR_EVENT = bluetooth.UUID("75200001-14D2-4CDA-8B6B-697C554C9311")
CHAR_SAVED_SESSION_ID_LIST = bluetooth.UUID("75200002-14D2-4CDA-8B6B-697C554C9311")
CHAR_RESERVED = bluetooth.UUID("75200003-14D2-4CDA-8B6B-697C554C9311")
CHAR_SHOT_LIST = bluetooth.UUID("75200004-14D2-4CDA-8B6B-697C554C9311")
CHAR_PAR_SETUP = bluetooth.UUID("75200005-14D2-4CDA-8B6B-697C554C9311")
CHAR_UNIX_TIME = bluetooth.UUID("75200006-14D2-4CDA-8B6B-697C554C9311")
CHAR_API_VERSION = bluetooth.UUID("7520FFFE-14D2-4CDA-8B6B-697C554C9311")

# Komendy
CMD_SESSION_START = const(0x00)
CMD_SESSION_SUSPEND = const(0x01)
CMD_SESSION_RESUME = const(0x02)
CMD_SESSION_STOP = const(0x03)

# Eventy
EVENT_SESSION_STARTED = const(0x00)
EVENT_SESSION_SUSPENDED = const(0x01)
EVENT_SESSION_RESUMED = const(0x02)
EVENT_SESSION_STOPPED = const(0x03)
EVENT_SHOT_DETECTED = const(0x04)
EVENT_SESSION_SET_BEGIN = const(0x05)

EVENT_NAMES = {
    0x00: "SESSION_STARTED",
    0x01: "SESSION_SUSPENDED",
    0x02: "SESSION_RESUMED",
    0x03: "SESSION_STOPPED",
    0x04: "SHOT_DETECTED",
    0x05: "SESSION_SET_BEGIN"
}


class SGTimerBLE:
    def __init__(self):
        self.connection = None
        self.service = None
        self.characteristics = {}

    async def scan_and_connect(self, timeout_ms=5000):
        """Skanuje i łączy się z urządzeniem SG Timer"""
        print("Skanowanie urządzeń BLE...")

        async with aioble.scan(timeout_ms, interval_us=30000, window_us=30000, active=True) as scanner:
            async for result in scanner:
                # Szukamy urządzenia zaczynającego się od SG-SST4
                if result.name() and result.name().startswith("SG-SST4"):
                    print(f"\nZnaleziono urządzenie: {result.name()}")
                    print(f"Adres: {result.device}")
                    print(f"RSSI: {result.rssi} dBm")

                    # Próba połączenia
                    try:
                        print("\nŁączenie...")
                        self.connection = await result.device.connect()
                        print("Połączono!")
                        return True
                    except asyncio.TimeoutError:
                        print("Timeout podczas łączenia")
                        return False

        print("Nie znaleziono urządzenia SG Timer")
        return False

    async def discover_services(self):
        """Odkrywa serwisy i charakterystyki"""
        if not self.connection:
            print("Brak połączenia")
            return False

        print("\nOdkrywanie serwisów...")
        try:
            self.service = await self.connection.service(SERVICE_UUID)
            print(f"Znaleziono serwis: {SERVICE_UUID}")

            # Odkryj wszystkie charakterystyki
            char_uuids = {
                "COMMAND": CHAR_COMMAND,
                "EVENT": CHAR_EVENT,
                "SAVED_SESSION_ID_LIST": CHAR_SAVED_SESSION_ID_LIST,
                "RESERVED": CHAR_RESERVED,
                "SHOT_LIST": CHAR_SHOT_LIST,
                "PAR_SETUP": CHAR_PAR_SETUP,
                "UNIX_TIME": CHAR_UNIX_TIME,
                "API_VERSION": CHAR_API_VERSION
            }

            for name, uuid in char_uuids.items():
                try:
                    char = await self.service.characteristic(uuid)
                    self.characteristics[name] = char
                    print(f"  ✓ {name}")
                except:
                    print(f"  ✗ {name} - nie znaleziono")

            return True
        except Exception as e:
            print(f"Błąd podczas odkrywania serwisów: {e}")
            return False

    async def read_api_version(self):
        """Odczytuje wersję API"""
        if "API_VERSION" not in self.characteristics:
            return None

        try:
            data = await self.characteristics["API_VERSION"].read()
            version = data.decode('ascii')
            print(f"\n📌 Wersja API: {version}")
            return version
        except Exception as e:
            print(f"Błąd odczytu API_VERSION: {e}")
            return None

    async def read_unix_time(self):
        """Odczytuje czas Unix z urządzenia"""
        if "UNIX_TIME" not in self.characteristics:
            return None

        try:
            data = await self.characteristics["UNIX_TIME"].read()
            unix_time = struct.unpack('>I', data)[0]  # Big Endian
            print(f"\n🕐 Czas Unix: {unix_time}")

            # Konwersja na czytelny format (uproszczony)
            from time import gmtime
            t = gmtime(unix_time)
            print(f"   Data: {t[0]}-{t[1]:02d}-{t[2]:02d} {t[3]:02d}:{t[4]:02d}:{t[5]:02d} UTC")
            return unix_time
        except Exception as e:
            print(f"Błąd odczytu UNIX_TIME: {e}")
            return None

    async def read_par_setup(self):
        """Odczytuje konfigurację PAR"""
        if "PAR_SETUP" not in self.characteristics:
            return None

        try:
            data = await self.characteristics["PAR_SETUP"].read()
            start_delay, time_limit, shot_limit = struct.unpack('>HHH', data)

            print(f"\n⚙️  Konfiguracja PAR:")
            if start_delay == 0xFFFF:
                print(f"   Start Delay: Losowy (1.0-4.0s)")
            else:
                print(f"   Start Delay: {start_delay * 0.1:.1f}s")

            if time_limit == 0:
                print(f"   Time Limit: Bez limitu")
            else:
                print(f"   Time Limit: {time_limit * 0.1:.1f}s")

            if shot_limit == 0:
                print(f"   Shot Limit: Bez limitu")
            else:
                print(f"   Shot Limit: {shot_limit} strzałów")

            return (start_delay, time_limit, shot_limit)
        except Exception as e:
            print(f"Błąd odczytu PAR_SETUP: {e}")
            return None

    async def read_saved_sessions(self):
        """Odczytuje listę zapisanych sesji"""
        if "SAVED_SESSION_ID_LIST" not in self.characteristics:
            return []

        try:
            # Rozpocznij od ostatniej sesji
            await self.characteristics["SAVED_SESSION_ID_LIST"].write(struct.pack('>I', 0xFFFFFFFF))

            sessions = []
            print(f"\n💾 Zapisane sesje:")

            while True:
                data = await self.characteristics["SAVED_SESSION_ID_LIST"].read()
                sess_id = struct.unpack('>I', data)[0]

                if sess_id == 0xFFFFFFFF:
                    break

                sessions.append(sess_id)
                from time import gmtime
                t = gmtime(sess_id)
                print(f"   Sesja ID: {sess_id} ({t[0]}-{t[1]:02d}-{t[2]:02d} {t[3]:02d}:{t[4]:02d}:{t[5]:02d})")

                # Limit bezpieczeństwa
                if len(sessions) > 50:
                    print("   ... (ograniczono do 50 sesji)")
                    break

            if not sessions:
                print("   Brak zapisanych sesji")

            return sessions
        except Exception as e:
            print(f"Błąd odczytu sesji: {e}")
            return []

    async def read_shots_for_session(self, session_id):
        """Odczytuje strzały dla danej sesji"""
        if "SHOT_LIST" not in self.characteristics:
            return []

        try:
            # Ustaw sesję
            await self.characteristics["SHOT_LIST"].write(struct.pack('>I', session_id))

            shots = []
            print(f"\n🎯 Strzały dla sesji {session_id}:")

            while True:
                data = await self.characteristics["SHOT_LIST"].read()
                shot_number, shot_time = struct.unpack('>HI', data)

                if shot_time == 0xFFFFFFFF:
                    break

                shots.append((shot_number, shot_time))
                print(f"   Strzał #{shot_number}: {shot_time}ms ({shot_time/1000:.3f}s)")

                # Limit bezpieczeństwa
                if len(shots) > 100:
                    print("   ... (ograniczono do 100 strzałów)")
                    break

            if not shots:
                print("   Brak strzałów w tej sesji")

            return shots
        except Exception as e:
            print(f"Błąd odczytu strzałów: {e}")
            return []

    async def subscribe_to_events(self):
        """Subskrybuje powiadomienia o eventach"""
        if "EVENT" not in self.characteristics:
            print("Charakterystyka EVENT niedostępna")
            return

        print("\n📡 Nasłuchiwanie eventów (naciśnij Ctrl+C aby zatrzymać)...")

        try:
            await self.characteristics["EVENT"].subscribe(notify=True)

            while True:
                data = await self.characteristics["EVENT"].notified()
                self.parse_event(data)

        except asyncio.CancelledError:
            print("\nZatrzymano nasłuchiwanie")
        except Exception as e:
            print(f"Błąd subskrypcji: {e}")

    def parse_event(self, data):
        """Parsuje dane eventu"""
        if len(data) < 2:
            return

        length = data[0]
        event_id = data[1]
        event_name = EVENT_NAMES.get(event_id, f"UNKNOWN_{event_id}")

        print(f"\n🔔 EVENT: {event_name}")

        if event_id == EVENT_SESSION_STARTED and len(data) >= 8:
            sess_id = struct.unpack('>I', data[2:6])[0]
            start_delay = struct.unpack('>H', data[6:8])[0]
            print(f"   Session ID: {sess_id}")
            print(f"   Start Delay: {start_delay * 0.1:.1f}s")

        elif event_id in [EVENT_SESSION_SUSPENDED, EVENT_SESSION_RESUMED, EVENT_SESSION_STOPPED] and len(data) >= 8:
            sess_id = struct.unpack('>I', data[2:6])[0]
            total_shots = struct.unpack('>H', data[6:8])[0]
            print(f"   Session ID: {sess_id}")
            print(f"   Total Shots: {total_shots}")

        elif event_id == EVENT_SHOT_DETECTED and len(data) >= 12:
            sess_id = struct.unpack('>I', data[2:6])[0]
            shot_num = struct.unpack('>H', data[6:8])[0]
            shot_time = struct.unpack('>I', data[8:12])[0]
            print(f"   Session ID: {sess_id}")
            print(f"   Shot #{shot_num}: {shot_time}ms ({shot_time/1000:.3f}s)")

        elif event_id == EVENT_SESSION_SET_BEGIN and len(data) >= 6:
            sess_id = struct.unpack('>I', data[2:6])[0]
            print(f"   Session ID: {sess_id}")

    async def read_all_data(self):
        """Odczytuje wszystkie dostępne dane"""
        print("\n" + "="*50)
        print("ZBIERANIE WSZYSTKICH DANYCH Z SG TIMER")
        print("="*50)

        await self.read_api_version()
        await self.read_unix_time()
        await self.read_par_setup()
        sessions = await self.read_saved_sessions()

        # Odczytaj strzały dla pierwszych 3 sesji (jeśli są)
        for i, session_id in enumerate(sessions[:3]):
            await self.read_shots_for_session(session_id)
            if i < len(sessions) - 1:
                await asyncio.sleep(0.5)

        print("\n" + "="*50)
        print("ZAKOŃCZONO ODCZYT DANYCH")
        print("="*50)

    async def disconnect(self):
        """Rozłącza połączenie"""
        if self.connection:
            await self.connection.disconnect()
            print("\nRozłączono")


async def main():
    """Główna funkcja aplikacji"""
    timer = SGTimerBLE()

    try:
        # Skanuj i łącz
        if not await timer.scan_and_connect(timeout_ms=10000):
            print("Nie udało się połączyć z urządzeniem")
            return

        # Odkryj serwisy
        if not await timer.discover_services():
            print("Nie udało się odkryć serwisów")
            await timer.disconnect()
            return

        # Odczytaj wszystkie dane
        await timer.read_all_data()

        # Opcjonalnie: nasłuchuj eventów (odkomentuj jeśli potrzebne)
        # await timer.subscribe_to_events()

    except KeyboardInterrupt:
        print("\n\nPrzerwano przez użytkownika")
    except Exception as e:
        print(f"\nBłąd: {e}")
    finally:
        await timer.disconnect()


if __name__ == "__main__":
    asyncio.run(main())
