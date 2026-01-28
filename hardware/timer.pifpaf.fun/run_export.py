"""
EKSPORT WSZYSTKICH SESJI - ULTRA PROSTY
Uruchom ten plik aby automatycznie wyeksportować wszystkie sesje do CSV
Użycie: import run_export
"""

import asyncio
from export_sessions import export_all_sessions_to_csv

# Po prostu uruchom eksport
asyncio.run(export_all_sessions_to_csv())
