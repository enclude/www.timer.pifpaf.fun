"""
Export All Sessions - Prosty skrypt do eksportu wszystkich sesji do CSV
Użycie: python export_sessions.py lub import export_sessions w REPL
"""

import asyncio
import struct
from sg_timer_ble import SGTimerBLE


async def export_all_sessions_to_csv():
    """Eksportuje wszystkie sesje z Timera do plików CSV"""

    print("\n" + "="*60)
    print("EKSPORT WSZYSTKICH SESJI DO CSV")
    print("="*60)

    timer = SGTimerBLE()

    try:
        # Połącz się z timerem
        print("\n[1/4] Łączenie z SG Timer...")
        if not await timer.scan_and_connect(timeout_ms=10000):
            print("❌ Nie można połączyć się z timerem")
            return

        # Odkryj serwisy
        print("[2/4] Odkrywanie serwisów...")
        if not await timer.discover_services():
            print("❌ Nie można odkryć serwisów")
            return

        # Sprawdź czy charakterystyki są dostępne
        if "SAVED_SESSION_ID_LIST" not in timer.characteristics:
            print("❌ Charakterystyka SAVED_SESSION_ID_LIST niedostępna")
            return

        if "SHOT_LIST" not in timer.characteristics:
            print("❌ Charakterystyka SHOT_LIST niedostępna")
            return

        # Odczytaj listę sesji
        print("[3/4] Odczytywanie listy sesji...")

        # Rozpocznij od ostatniej sesji
        await timer.characteristics["SAVED_SESSION_ID_LIST"].write(
            struct.pack('>I', 0xFFFFFFFF)
        )

        sessions = []
        while True:
            data = await timer.characteristics["SAVED_SESSION_ID_LIST"].read()
            sess_id = struct.unpack('>I', data)[0]

            if sess_id == 0xFFFFFFFF:
                break

            sessions.append(sess_id)

            # Zabezpieczenie
            if len(sessions) > 100:
                print("⚠️  Ograniczono do 100 sesji")
                break

        if not sessions:
            print("ℹ️  Brak sesji do eksportu")
            await timer.disconnect()
            return

        print(f"✓ Znaleziono {len(sessions)} sesji")

        # Eksportuj każdą sesję
        print(f"[4/4] Eksportowanie sesji do CSV...")
        print("")

        exported_count = 0
        total_shots = 0

        for i, session_id in enumerate(sessions, 1):
            # Formatuj datę
            from time import gmtime
            t = gmtime(session_id)
            date_str = f"{t[0]}-{t[1]:02d}-{t[2]:02d}_{t[3]:02d}-{t[4]:02d}-{t[5]:02d}"

            print(f"  [{i}/{len(sessions)}] Sesja {session_id} ({date_str})...", end=" ")

            # Ustaw sesję do odczytu
            await timer.characteristics["SHOT_LIST"].write(
                struct.pack('>I', session_id)
            )

            # Odczytaj strzały
            shots = []
            while True:
                shot_data = await timer.characteristics["SHOT_LIST"].read()
                shot_number, shot_time = struct.unpack('>HI', shot_data)

                if shot_time == 0xFFFFFFFF:
                    break

                shots.append((shot_number, shot_time))

                # Zabezpieczenie
                if len(shots) > 500:
                    break

            if not shots:
                print("Brak strzałów")
                continue

            # Zapisz do CSV
            filename = f"session_{session_id}_{date_str}.csv"

            try:
                with open(filename, 'w') as f:
                    # Nagłówek
                    f.write("Shot_Number,Time_ms,Time_s,Split_ms,Split_s\n")

                    # Dane
                    prev_time = 0
                    for shot_num, shot_time in shots:
                        split = shot_time - prev_time if shot_num > 0 else 0
                        f.write(f"{shot_num},{shot_time},{shot_time/1000:.3f},")
                        f.write(f"{split},{split/1000:.3f}\n")
                        prev_time = shot_time

                print(f"✓ {len(shots)} strzałów → {filename}")
                exported_count += 1
                total_shots += len(shots)

            except Exception as e:
                print(f"❌ Błąd: {e}")

            # Krótka pauza między sesjami
            await asyncio.sleep(0.2)

        # Stwórz plik podsumowujący
        print("\n[Bonus] Tworzenie pliku podsumowującego...")

        try:
            summary_file = "sessions_summary.csv"

            with open(summary_file, 'w') as f:
                # Nagłówek
                f.write("Session_ID,Date_Time,Total_Shots,Total_Time_s,")
                f.write("First_Shot_s,Last_Shot_s,Avg_Split_s,Min_Split_s,Max_Split_s\n")

                # Ponownie przejdź przez sesje i zbierz statystyki
                for session_id in sessions:
                    # Ustaw sesję
                    await timer.characteristics["SHOT_LIST"].write(
                        struct.pack('>I', session_id)
                    )

                    # Odczytaj strzały
                    shots = []
                    while True:
                        shot_data = await timer.characteristics["SHOT_LIST"].read()
                        shot_number, shot_time = struct.unpack('>HI', shot_data)

                        if shot_time == 0xFFFFFFFF:
                            break

                        shots.append(shot_time)

                        if len(shots) > 500:
                            break

                    if not shots:
                        continue

                    # Oblicz statystyki
                    total_shot_count = len(shots)
                    first_shot = shots[0] / 1000.0
                    last_shot = shots[-1] / 1000.0
                    total_time = last_shot

                    # Splity
                    splits = []
                    for j in range(1, len(shots)):
                        split = (shots[j] - shots[j-1]) / 1000.0
                        splits.append(split)

                    avg_split = (sum(splits) / len(splits)) if splits else 0
                    min_split = min(splits) if splits else 0
                    max_split = max(splits) if splits else 0

                    # Data sesji
                    t = gmtime(session_id)
                    date_time = f"{t[0]}-{t[1]:02d}-{t[2]:02d} {t[3]:02d}:{t[4]:02d}:{t[5]:02d}"

                    # Zapisz wiersz
                    f.write(f"{session_id},{date_time},{total_shot_count},")
                    f.write(f"{total_time:.3f},{first_shot:.3f},{last_shot:.3f},")
                    f.write(f"{avg_split:.3f},{min_split:.3f},{max_split:.3f}\n")

                    await asyncio.sleep(0.1)

            print(f"✓ Raport podsumowujący → {summary_file}")

        except Exception as e:
            print(f"⚠️  Nie udało się stworzyć raportu: {e}")

        # Podsumowanie
        print("\n" + "="*60)
        print("EKSPORT ZAKOŃCZONY")
        print("="*60)
        print(f"✓ Wyeksportowano: {exported_count} sesji")
        print(f"✓ Łącznie strzałów: {total_shots}")
        print(f"✓ Pliki CSV zapisane w bieżącym katalogu")
        print("")

    except Exception as e:
        print(f"\n❌ Błąd: {e}")
        import sys
        sys.print_exception(e)

    finally:
        await timer.disconnect()
        print("Rozłączono")


async def main():
    """Główna funkcja"""
    await export_all_sessions_to_csv()


# Możliwość uruchomienia bezpośrednio
if __name__ == "__main__":
    asyncio.run(main())
