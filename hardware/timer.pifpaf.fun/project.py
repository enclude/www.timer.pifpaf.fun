"""
SG Timer BLE Reader - Główny plik projektu
Uruchamia aplikację do zbierania danych z Smart Shot Timer przez BLE
"""

import asyncio
from sg_timer_ble import main

# Uruchom główną aplikację
asyncio.run(main())
