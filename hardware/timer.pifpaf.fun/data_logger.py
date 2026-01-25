"""
Data Logger - zapisywanie danych z SG Timer do plików CSV
"""

import asyncio
import struct
from sg_timer_ble import SGTimerBLE


class SGTimerDataLogger(SGTimerBLE):
    """Rozszerzona klasa z logowaniem danych do plików"""

    def __init__(self, log_dir="/"):
        super().__init__()
        self.log_dir = log_dir
        self.current_session_file = None
        self.session_shots = []

    def _format_timestamp(self, unix_time):
        """Formatuje timestamp Unix do czytelnej daty"""
        from time import gmtime
        t = gmtime(unix_time)
        return f"{t[0]}-{t[1]:02d}-{t[2]:02d}_{t[3]:02d}-{t[4]:02d}-{t[5]:02d}"

    async def export_session_to_csv(self, session_id):
        """
        Eksportuje sesję do pliku CSV

        Args:
            session_id: ID sesji (unix timestamp)
        """
        if "SHOT_LIST" not in self.characteristics:
            print("Charakterystyka SHOT_LIST niedostępna")
            return False

        try:
            # Odczytaj strzały
            await self.characteristics["SHOT_LIST"].write(struct.pack('>I', session_id))

            shots = []
            while True:
                data = await self.characteristics["SHOT_LIST"].read()
                shot_number, shot_time = struct.unpack('>HI', data)

                if shot_time == 0xFFFFFFFF:
                    break

                shots.append((shot_number, shot_time))

                if len(shots) > 1000:  # Limit bezpieczeństwa
                    break

            if not shots:
                print(f"Brak strzałów w sesji {session_id}")
                return False

            # Stwórz nazwę pliku
            timestamp_str = self._format_timestamp(session_id)
            filename = f"{self.log_dir}/session_{session_id}_{timestamp_str}.csv"

            print(f"\n💾 Zapisywanie do: {filename}")

            # Zapisz do CSV
            with open(filename, 'w') as f:
                # Nagłówek
                f.write("Shot_Number,Time_ms,Time_s,Split_ms,Split_s\n")

                # Dane
                prev_time = 0
                for shot_num, shot_time in shots:
                    split = shot_time - prev_time if shot_num > 0 else 0
                    f.write(f"{shot_num},{shot_time},{shot_time/1000:.3f},{split},{split/1000:.3f}\n")
                    prev_time = shot_time

            print(f"✅ Zapisano {len(shots)} strzałów")
            return True

        except Exception as e:
            print(f"Błąd eksportu: {e}")
            return False

    async def export_all_sessions(self):
        """Eksportuje wszystkie zapisane sesje do plików CSV"""
        if "SAVED_SESSION_ID_LIST" not in self.characteristics:
            print("Charakterystyka SAVED_SESSION_ID_LIST niedostępna")
            return

        try:
            # Rozpocznij od ostatniej sesji
            await self.characteristics["SAVED_SESSION_ID_LIST"].write(struct.pack('>I', 0xFFFFFFFF))

            sessions = []
            while True:
                data = await self.characteristics["SAVED_SESSION_ID_LIST"].read()
                sess_id = struct.unpack('>I', data)[0]

                if sess_id == 0xFFFFFFFF:
                    break

                sessions.append(sess_id)

                if len(sessions) > 100:  # Limit
                    break

            print(f"\n📊 Znaleziono {len(sessions)} sesji do eksportu")

            # Eksportuj każdą sesję
            for i, session_id in enumerate(sessions, 1):
                print(f"\n[{i}/{len(sessions)}] Sesja {session_id}")
                await self.export_session_to_csv(session_id)
                await asyncio.sleep(0.2)  # Krótka pauza

            print(f"\n✅ Zakończono eksport wszystkich sesji")

        except Exception as e:
            print(f"Błąd: {e}")

    async def create_summary_report(self):
        """Tworzy plik podsumowujący wszystkie sesje"""
        if "SAVED_SESSION_ID_LIST" not in self.characteristics:
            return

        try:
            # Odczytaj wszystkie sesje
            await self.characteristics["SAVED_SESSION_ID_LIST"].write(struct.pack('>I', 0xFFFFFFFF))

            filename = f"{self.log_dir}/sessions_summary.csv"
            print(f"\n📋 Tworzenie raportu: {filename}")

            with open(filename, 'w') as f:
                # Nagłówek
                f.write("Session_ID,Date_Time,Total_Shots,Total_Time_s,Avg_Split_s,Min_Split_s,Max_Split_s\n")

                session_count = 0
                while True:
                    # Odczytaj ID sesji
                    data = await self.characteristics["SAVED_SESSION_ID_LIST"].read()
                    sess_id = struct.unpack('>I', data)[0]

                    if sess_id == 0xFFFFFFFF:
                        break

                    # Odczytaj strzały dla tej sesji
                    await self.characteristics["SHOT_LIST"].write(struct.pack('>I', sess_id))

                    shots = []
                    while True:
                        shot_data = await self.characteristics["SHOT_LIST"].read()
                        shot_number, shot_time = struct.unpack('>HI', shot_data)

                        if shot_time == 0xFFFFFFFF:
                            break

                        shots.append(shot_time)

                        if len(shots) > 1000:
                            break

                    if shots:
                        # Oblicz statystyki
                        total_shots = len(shots)
                        total_time = shots[-1] / 1000.0  # w sekundach

                        # Splity
                        splits = [shots[i] - shots[i-1] for i in range(1, len(shots))]
                        avg_split = (sum(splits) / len(splits)) / 1000.0 if splits else 0
                        min_split = (min(splits) / 1000.0) if splits else 0
                        max_split = (max(splits) / 1000.0) if splits else 0

                        # Data sesji
                        date_time = self._format_timestamp(sess_id).replace('_', ' ')

                        # Zapisz wiersz
                        f.write(f"{sess_id},{date_time},{total_shots},{total_time:.3f},")
                        f.write(f"{avg_split:.3f},{min_split:.3f},{max_split:.3f}\n")

                        session_count += 1
                        print(f"  ✓ Sesja {sess_id}: {total_shots} strzałów")

                    if session_count > 100:  # Limit
                        break

            print(f"\n✅ Zapisano raport: {session_count} sesji")

        except Exception as e:
            print(f"Błąd tworzenia raportu: {e}")

    async def log_live_session(self, filename=None):
        """
        Loguje sesję w czasie rzeczywistym do pliku CSV

        Args:
            filename: nazwa pliku (None = automatyczna)
        """
        if "EVENT" not in self.characteristics:
            print("Charakterystyka EVENT niedostępna")
            return

        # Automatyczna nazwa pliku jeśli nie podano
        if filename is None:
            import time
            timestamp = int(time.time())
            timestamp_str = self._format_timestamp(timestamp)
            filename = f"{self.log_dir}/live_session_{timestamp_str}.csv"

        print(f"\n📝 Logowanie na żywo do: {filename}")
        print("📡 Nasłuchiwanie eventów (Ctrl+C aby zatrzymać)...")

        try:
            # Otwórz plik
            with open(filename, 'w') as f:
                # Nagłówek
                f.write("Event_Type,Timestamp,Session_ID,Shot_Number,Time_ms,Time_s,Split_ms,Split_s\n")

                session_id = None
                last_shot_time = 0
                shot_count = 0

                # Subskrybuj eventy
                await self.characteristics["EVENT"].subscribe(notify=True)

                while True:
                    data = await self.characteristics["EVENT"].notified()
                    if len(data) < 2:
                        continue

                    event_id = data[1]
                    import time
                    timestamp = int(time.time())

                    if event_id == 0x00:  # SESSION_STARTED
                        session_id = struct.unpack('>I', data[2:6])[0]
                        print(f"\n🎬 SESJA ROZPOCZĘTA (ID: {session_id})")
                        f.write(f"SESSION_STARTED,{timestamp},{session_id},,,,,\n")
                        last_shot_time = 0
                        shot_count = 0

                    elif event_id == 0x04:  # SHOT_DETECTED
                        shot_num = struct.unpack('>H', data[6:8])[0]
                        shot_time = struct.unpack('>I', data[8:12])[0]

                        split = shot_time - last_shot_time if shot_num > 0 else 0

                        print(f"🎯 Strzał #{shot_num}: {shot_time/1000:.3f}s (split: {split/1000:.3f}s)")

                        f.write(f"SHOT_DETECTED,{timestamp},{session_id},{shot_num},")
                        f.write(f"{shot_time},{shot_time/1000:.3f},{split},{split/1000:.3f}\n")
                        f.flush()  # Zapisz natychmiast

                        last_shot_time = shot_time
                        shot_count += 1

                    elif event_id == 0x03:  # SESSION_STOPPED
                        total_shots = struct.unpack('>H', data[6:8])[0]
                        print(f"\n⏹️  SESJA ZAKOŃCZONA ({total_shots} strzałów)")
                        f.write(f"SESSION_STOPPED,{timestamp},{session_id},{total_shots},,,,\n")
                        f.flush()

        except KeyboardInterrupt:
            print("\n\nZatrzymano logowanie")
        except Exception as e:
            print(f"\nBłąd logowania: {e}")


# ============================================================================
# PRZYKŁADY UŻYCIA
# ============================================================================

async def example_export_all_sessions():
    """Eksportuje wszystkie sesje do oddzielnych plików CSV"""
    logger = SGTimerDataLogger()

    try:
        if not await logger.scan_and_connect():
            return

        await logger.discover_services()
        await logger.export_all_sessions()

    finally:
        await logger.disconnect()


async def example_create_summary():
    """Tworzy plik podsumowujący wszystkie sesje"""
    logger = SGTimerDataLogger()

    try:
        if not await logger.scan_and_connect():
            return

        await logger.discover_services()
        await logger.create_summary_report()

    finally:
        await logger.disconnect()


async def example_live_logging():
    """Loguje sesję w czasie rzeczywistym"""
    logger = SGTimerDataLogger()

    try:
        if not await logger.scan_and_connect():
            return

        await logger.discover_services()

        print("\nUruchom sesję na timerze, a dane będą logowane automatycznie...")
        await logger.log_live_session()

    finally:
        await logger.disconnect()


async def main():
    """Menu główne"""
    print("\n" + "="*60)
    print("SG TIMER DATA LOGGER")
    print("="*60)
    print("\nWybierz opcję:")
    print("1. Eksportuj wszystkie sesje do CSV")
    print("2. Stwórz raport podsumowujący")
    print("3. Loguj sesję na żywo")
    print("0. Wyjście")

    choice = input("\nWybór: ")

    if choice == "1":
        await example_export_all_sessions()
    elif choice == "2":
        await example_create_summary()
    elif choice == "3":
        await example_live_logging()
    elif choice == "0":
        print("Zakończono")
    else:
        print("Nieprawidłowy wybór")


if __name__ == "__main__":
    asyncio.run(main())
