<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SG Timer - Odczyt sesji strzeleckiej</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%230f3d3e'/><circle cx='50' cy='50' r='3' fill='white'/><circle cx='50' cy='50' r='35' fill='none' stroke='white' stroke-width='2'/><line x1='50' y1='15' x2='50' y2='25' stroke='white' stroke-width='3'/><line x1='50' y1='50' x2='50' y2='25' stroke='white' stroke-width='2'/><line x1='50' y1='50' x2='70' y2='50' stroke='white' stroke-width='2'/></svg>">
    <style>
        :root {
            --primary: #0f3d3e;
            --primary-hover: #145252;
            --accent: #1f6e5c;
            --bg-body: #f6fbfa;
            --bg-hover: #f0faf6;
            --text-primary: #0a0f0f;
            --text-secondary: #516667;
            --border-light: #dbe9e6;
            --border-pale: #e8f0ee;
            --alert-red: #d9534f;
            --alert-red-hover: #c9302c;
            --shadow: rgba(0,0,0,.08);
            --shadow-light: rgba(0,0,0,.05);
            --focus-glow: rgba(31, 110, 92, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: var(--primary);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px var(--shadow);
        }

        header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            text-align: center;
        }

        header p {
            text-align: center;
            opacity: 0.9;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .card {
            background: white;
            border-radius: 8px;
            border: 1px solid var(--border-pale);
            box-shadow: 0 2px 8px var(--shadow-light);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h2 {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-light);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-hover);
        }

        .btn-accent {
            background: var(--accent);
            color: white;
        }

        .btn-accent:hover:not(:disabled) {
            background: var(--primary);
        }

        .btn-danger {
            background: var(--alert-red);
            color: white;
        }

        .btn-danger:hover:not(:disabled) {
            background: var(--alert-red-hover);
        }

        .btn-outline {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover:not(:disabled) {
            background: var(--bg-hover);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-disconnected {
            background: #fee;
            color: var(--alert-red);
        }

        .status-connecting {
            background: #fff3cd;
            color: #856404;
        }

        .status-connected {
            background: #d4edda;
            color: #155724;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-connecting .status-dot {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .device-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .info-item {
            padding: 12px;
            background: var(--bg-hover);
            border-radius: 6px;
            border: 1px solid var(--border-light);
        }

        .info-item label {
            display: block;
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }

        .info-item span {
            font-weight: 600;
            color: var(--text-primary);
        }

        .session-list {
            list-style: none;
            max-height: 300px;
            overflow-y: auto;
        }

        .session-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .session-item:hover {
            background: var(--bg-hover);
            border-color: var(--accent);
        }

        .session-item.active {
            background: var(--bg-hover);
            border-color: var(--primary);
            border-width: 2px;
        }

        .session-date {
            font-weight: 600;
        }

        .session-id {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .shots-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .shots-table th,
        .shots-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }

        .shots-table th {
            background: var(--bg-hover);
            font-weight: 600;
            color: var(--primary);
        }

        .shots-table tr:hover td {
            background: var(--bg-hover);
        }

        .shots-table .shot-num {
            font-weight: 600;
            color: var(--accent);
        }

        .shots-table .shot-time {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 1.1rem;
        }

        .shots-table .split-time {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .live-display {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            color: white;
            margin-bottom: 20px;
        }

        .live-display .current-time {
            font-size: 4rem;
            font-weight: 700;
            font-family: 'Monaco', 'Consolas', monospace;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .live-display .shot-count {
            font-size: 1.5rem;
            margin-top: 10px;
            opacity: 0.9;
        }

        .live-display .session-status {
            font-size: 1rem;
            margin-top: 5px;
            opacity: 0.8;
        }

        .controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert-info {
            background: var(--bg-hover);
            color: var(--text-primary);
            border: 1px solid var(--border-light);
        }

        .alert-error {
            background: #fee;
            color: var(--alert-red);
            border: 1px solid #fcc;
        }

        .hidden {
            display: none !important;
        }

        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            color: var(--text-secondary);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-light);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-top: 30px;
        }

        footer a {
            color: var(--primary);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .live-display .current-time {
                font-size: 2.5rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .controls {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>SG Timer - Monitor sesji</h1>
            <p>Odczyt danych z timera strzeleckiego przez Bluetooth</p>
        </div>
    </header>

    <main class="container">
        <!-- Browser compatibility warning -->
        <div id="browserWarning" class="alert alert-warning hidden">
            <strong>Uwaga:</strong> Web Bluetooth API wymaga przegladarki Chrome, Edge lub Opera.
            Firefox i Safari nie sa wspierane.
        </div>

        <!-- Connection Card -->
        <div class="card">
            <h2>Polaczenie Bluetooth</h2>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div id="connectionStatus" class="status-indicator status-disconnected">
                    <span class="status-dot"></span>
                    <span id="statusText">Rozlaczony</span>
                </div>

                <div class="btn-group">
                    <button id="btnConnect" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6.5 6.5l11 11L12 23V1l5.5 5.5-11 11"/>
                        </svg>
                        Polacz z timerem
                    </button>
                    <button id="btnDisconnect" class="btn btn-outline hidden">
                        Rozlacz
                    </button>
                </div>
            </div>

            <div id="deviceInfo" class="device-info hidden">
                <div class="info-item">
                    <label>Nazwa urzadzenia</label>
                    <span id="deviceName">-</span>
                </div>
                <div class="info-item">
                    <label>Wersja API</label>
                    <span id="apiVersion">-</span>
                </div>
                <div class="info-item">
                    <label>Czas urzadzenia</label>
                    <span id="deviceTime">-</span>
                </div>
            </div>
        </div>

        <!-- Live Session Display -->
        <div id="liveSection" class="hidden">
            <div class="live-display">
                <div id="sessionStatus" class="session-status">Oczekiwanie na sesje...</div>
                <div id="currentTime" class="current-time">0.000</div>
                <div id="shotCount" class="shot-count">Strzaly: 0</div>
            </div>

            <div class="controls">
                <button id="btnStart" class="btn btn-accent">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    Start
                </button>
                <button id="btnStop" class="btn btn-danger" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="6" y="6" width="12" height="12"/>
                    </svg>
                    Stop
                </button>
            </div>
        </div>

        <!-- Saved Sessions -->
        <div id="sessionsCard" class="card hidden">
            <h2>Zapisane sesje</h2>

            <button id="btnLoadSessions" class="btn btn-outline" style="margin-bottom: 15px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 4v6h-6M1 20v-6h6"/>
                    <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                </svg>
                Odswiezaj liste sesji
            </button>

            <div id="sessionsLoading" class="loading hidden">
                <div class="spinner"></div>
                Ladowanie sesji...
            </div>

            <ul id="sessionList" class="session-list"></ul>

            <div id="noSessions" class="empty-state hidden">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
                <p>Brak zapisanych sesji</p>
            </div>
        </div>

        <!-- Shot List -->
        <div id="shotsCard" class="card hidden">
            <h2>Lista strzalow - <span id="selectedSessionDate">-</span></h2>

            <div id="shotsLoading" class="loading hidden">
                <div class="spinner"></div>
                Ladowanie strzalow...
            </div>

            <table id="shotsTable" class="shots-table hidden">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Czas</th>
                        <th>Split</th>
                    </tr>
                </thead>
                <tbody id="shotsBody"></tbody>
            </table>

            <div id="noShots" class="empty-state hidden">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="3"/>
                    <circle cx="12" cy="12" r="8"/>
                    <circle cx="12" cy="12" r="11"/>
                </svg>
                <p>Brak strzalow w tej sesji</p>
            </div>
        </div>

        <!-- Live Shots during session -->
        <div id="liveShotsCard" class="card hidden">
            <h2>Strzaly w biezacej sesji</h2>
            <table class="shots-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Czas</th>
                        <th>Split</th>
                    </tr>
                </thead>
                <tbody id="liveShotsBody"></tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>SG Timer Monitor &copy; <?php echo date('Y'); ?> |
        <a href="https://pifpaf.fun" target="_blank">pifpaf.fun</a> |
        <a href="https://github.com/enclude/www.timer.pifpaf.fun" target="_blank">GitHub</a></p>
        <p style="margin-top: 5px; font-size: 0.8rem;">
            Kompatybilny z SG Timer Sport i SG Timer GO (BLE API 3.2)
        </p>
    </footer>

    <script>
    // BLE UUIDs
    const BLE_SERVICE_UUID = '7520ffff-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_COMMAND = '75200000-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_EVENT = '75200001-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_SESSION_LIST = '75200002-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_SHOT_LIST = '75200004-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_UNIX_TIME = '75200006-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_API_VERSION = '7520fffe-14d2-4cda-8b6b-697c554c9311';

    // Command IDs
    const CMD_SESSION_START = 0x00;
    const CMD_SESSION_SUSPEND = 0x01;
    const CMD_SESSION_RESUME = 0x02;
    const CMD_SESSION_STOP = 0x03;

    // Event IDs
    const EVT_SESSION_STARTED = 0x00;
    const EVT_SESSION_SUSPENDED = 0x01;
    const EVT_SESSION_RESUMED = 0x02;
    const EVT_SESSION_STOPPED = 0x03;
    const EVT_SHOT_DETECTED = 0x04;
    const EVT_SESSION_SET_BEGIN = 0x05;

    // State
    let device = null;
    let server = null;
    let service = null;
    let characteristics = {};
    let currentSession = {
        id: null,
        active: false,
        shots: [],
        lastShotTime: 0
    };

    // DOM Elements
    const elements = {
        browserWarning: document.getElementById('browserWarning'),
        connectionStatus: document.getElementById('connectionStatus'),
        statusText: document.getElementById('statusText'),
        btnConnect: document.getElementById('btnConnect'),
        btnDisconnect: document.getElementById('btnDisconnect'),
        deviceInfo: document.getElementById('deviceInfo'),
        deviceName: document.getElementById('deviceName'),
        apiVersion: document.getElementById('apiVersion'),
        deviceTime: document.getElementById('deviceTime'),
        liveSection: document.getElementById('liveSection'),
        sessionStatus: document.getElementById('sessionStatus'),
        currentTime: document.getElementById('currentTime'),
        shotCount: document.getElementById('shotCount'),
        btnStart: document.getElementById('btnStart'),
        btnStop: document.getElementById('btnStop'),
        sessionsCard: document.getElementById('sessionsCard'),
        btnLoadSessions: document.getElementById('btnLoadSessions'),
        sessionsLoading: document.getElementById('sessionsLoading'),
        sessionList: document.getElementById('sessionList'),
        noSessions: document.getElementById('noSessions'),
        shotsCard: document.getElementById('shotsCard'),
        selectedSessionDate: document.getElementById('selectedSessionDate'),
        shotsLoading: document.getElementById('shotsLoading'),
        shotsTable: document.getElementById('shotsTable'),
        shotsBody: document.getElementById('shotsBody'),
        noShots: document.getElementById('noShots'),
        liveShotsCard: document.getElementById('liveShotsCard'),
        liveShotsBody: document.getElementById('liveShotsBody')
    };

    // Check Web Bluetooth support
    function checkBrowserSupport() {
        if (!navigator.bluetooth) {
            elements.browserWarning.classList.remove('hidden');
            elements.btnConnect.disabled = true;
            return false;
        }
        return true;
    }

    // Update connection status UI
    function updateConnectionStatus(status, text) {
        elements.connectionStatus.className = 'status-indicator status-' + status;
        elements.statusText.textContent = text;
    }

    // Format time in seconds to display format
    function formatTime(ms) {
        const seconds = ms / 1000;
        return seconds.toFixed(3);
    }

    // Format Unix timestamp to date
    function formatDate(timestamp) {
        const date = new Date(timestamp * 1000);
        return date.toLocaleString('pl-PL', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    // Parse Big Endian bytes to number
    function parseBigEndian(dataView, offset, bytes) {
        let value = 0;
        for (let i = 0; i < bytes; i++) {
            value = (value << 8) | dataView.getUint8(offset + i);
        }
        return value;
    }

    // Connect to device
    async function connect() {
        try {
            updateConnectionStatus('connecting', 'Laczenie...');
            elements.btnConnect.disabled = true;

            // Request device
            device = await navigator.bluetooth.requestDevice({
                filters: [
                    { namePrefix: 'SG-SST4' }
                ],
                optionalServices: [BLE_SERVICE_UUID]
            });

            device.addEventListener('gattserverdisconnected', onDisconnected);

            // Connect to GATT server
            server = await device.gatt.connect();

            // Get service
            service = await server.getPrimaryService(BLE_SERVICE_UUID);

            // Get characteristics
            characteristics.command = await service.getCharacteristic(BLE_CHAR_COMMAND);
            characteristics.event = await service.getCharacteristic(BLE_CHAR_EVENT);
            characteristics.sessionList = await service.getCharacteristic(BLE_CHAR_SESSION_LIST);
            characteristics.shotList = await service.getCharacteristic(BLE_CHAR_SHOT_LIST);
            characteristics.unixTime = await service.getCharacteristic(BLE_CHAR_UNIX_TIME);
            characteristics.apiVersion = await service.getCharacteristic(BLE_CHAR_API_VERSION);

            // Subscribe to notifications
            await characteristics.command.startNotifications();
            characteristics.command.addEventListener('characteristicvaluechanged', onCommandResponse);

            await characteristics.event.startNotifications();
            characteristics.event.addEventListener('characteristicvaluechanged', onEventReceived);

            // Update UI
            updateConnectionStatus('connected', 'Polaczony');
            elements.btnConnect.classList.add('hidden');
            elements.btnDisconnect.classList.remove('hidden');
            elements.deviceInfo.classList.remove('hidden');
            elements.liveSection.classList.remove('hidden');
            elements.sessionsCard.classList.remove('hidden');

            // Read device info
            elements.deviceName.textContent = device.name || 'Nieznane';
            await readApiVersion();
            await readDeviceTime();

            console.log('Connected to', device.name);

        } catch (error) {
            console.error('Connection error:', error);
            updateConnectionStatus('disconnected', 'Blad polaczenia');
            elements.btnConnect.disabled = false;

            if (error.name !== 'NotFoundError') {
                alert('Blad polaczenia: ' + error.message);
            }
        }
    }

    // Disconnect from device
    function disconnect() {
        if (device && device.gatt.connected) {
            device.gatt.disconnect();
        }
    }

    // Handle disconnection
    function onDisconnected() {
        updateConnectionStatus('disconnected', 'Rozlaczony');
        elements.btnConnect.classList.remove('hidden');
        elements.btnConnect.disabled = false;
        elements.btnDisconnect.classList.add('hidden');
        elements.deviceInfo.classList.add('hidden');
        elements.liveSection.classList.add('hidden');
        elements.sessionsCard.classList.add('hidden');
        elements.shotsCard.classList.add('hidden');
        elements.liveShotsCard.classList.add('hidden');

        device = null;
        server = null;
        service = null;
        characteristics = {};
        currentSession = { id: null, active: false, shots: [], lastShotTime: 0 };

        console.log('Disconnected');
    }

    // Read API version
    async function readApiVersion() {
        try {
            const value = await characteristics.apiVersion.readValue();
            const decoder = new TextDecoder('utf-8');
            elements.apiVersion.textContent = decoder.decode(value);
        } catch (error) {
            console.error('Error reading API version:', error);
            elements.apiVersion.textContent = 'Blad odczytu';
        }
    }

    // Read device time
    async function readDeviceTime() {
        try {
            const value = await characteristics.unixTime.readValue();
            const timestamp = parseBigEndian(value, 0, 4);
            elements.deviceTime.textContent = formatDate(timestamp);
        } catch (error) {
            console.error('Error reading device time:', error);
            elements.deviceTime.textContent = 'Blad odczytu';
        }
    }

    // Handle command response
    function onCommandResponse(event) {
        const value = event.target.value;
        const len = value.getUint8(0);
        const cmdId = value.getUint8(1);
        const respCode = value.getUint8(2);

        console.log('Command response:', { cmdId, respCode: respCode === 0 ? 'Success' : 'Error' });
    }

    // Handle event received
    function onEventReceived(event) {
        const value = event.target.value;
        const len = value.getUint8(0);
        const eventId = value.getUint8(1);

        switch (eventId) {
            case EVT_SESSION_STARTED:
                handleSessionStarted(value);
                break;
            case EVT_SESSION_SUSPENDED:
                handleSessionSuspended(value);
                break;
            case EVT_SESSION_RESUMED:
                handleSessionResumed(value);
                break;
            case EVT_SESSION_STOPPED:
                handleSessionStopped(value);
                break;
            case EVT_SHOT_DETECTED:
                handleShotDetected(value);
                break;
            case EVT_SESSION_SET_BEGIN:
                handleSessionSetBegin(value);
                break;
        }
    }

    // Handle session started event
    function handleSessionStarted(value) {
        const sessId = parseBigEndian(value, 2, 4);
        const startDelay = parseBigEndian(value, 6, 2) / 10;

        currentSession = {
            id: sessId,
            active: true,
            shots: [],
            lastShotTime: 0
        };

        elements.sessionStatus.textContent = `Sesja rozpoczeta (opoznienie: ${startDelay}s)`;
        elements.currentTime.textContent = '0.000';
        elements.shotCount.textContent = 'Strzaly: 0';
        elements.btnStart.disabled = true;
        elements.btnStop.disabled = false;
        elements.liveShotsCard.classList.remove('hidden');
        elements.liveShotsBody.innerHTML = '';

        console.log('Session started:', { sessId, startDelay });
    }

    // Handle session suspended event
    function handleSessionSuspended(value) {
        const sessId = parseBigEndian(value, 2, 4);
        const totalShots = parseBigEndian(value, 6, 2);

        currentSession.active = false;
        elements.sessionStatus.textContent = `Sesja wstrzymana (${totalShots} strzalow)`;
        elements.btnStart.disabled = false;
        elements.btnStop.disabled = true;

        console.log('Session suspended:', { sessId, totalShots });
    }

    // Handle session resumed event
    function handleSessionResumed(value) {
        const sessId = parseBigEndian(value, 2, 4);
        const totalShots = parseBigEndian(value, 6, 2);

        currentSession.active = true;
        elements.sessionStatus.textContent = 'Sesja wznowiona';
        elements.btnStart.disabled = true;
        elements.btnStop.disabled = false;

        console.log('Session resumed:', { sessId, totalShots });
    }

    // Handle session stopped event
    function handleSessionStopped(value) {
        const sessId = parseBigEndian(value, 2, 4);
        const totalShots = parseBigEndian(value, 6, 2);

        currentSession.active = false;
        elements.sessionStatus.textContent = `Sesja zakonczona (${totalShots} strzalow)`;
        elements.btnStart.disabled = false;
        elements.btnStop.disabled = true;

        console.log('Session stopped:', { sessId, totalShots });
    }

    // Handle shot detected event
    function handleShotDetected(value) {
        const sessId = parseBigEndian(value, 2, 4);
        const shotNum = parseBigEndian(value, 6, 2);
        const shotTime = parseBigEndian(value, 8, 4);

        const split = currentSession.shots.length > 0
            ? shotTime - currentSession.lastShotTime
            : shotTime;

        currentSession.shots.push({ num: shotNum, time: shotTime, split: split });
        currentSession.lastShotTime = shotTime;

        // Update live display
        elements.currentTime.textContent = formatTime(shotTime);
        elements.shotCount.textContent = `Strzaly: ${shotNum}`;

        // Add to live shots table
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="shot-num">${shotNum}</td>
            <td class="shot-time">${formatTime(shotTime)}s</td>
            <td class="split-time">${shotNum === 1 ? '-' : '+' + formatTime(split) + 's'}</td>
        `;
        elements.liveShotsBody.insertBefore(row, elements.liveShotsBody.firstChild);

        console.log('Shot detected:', { shotNum, shotTime, split });
    }

    // Handle session set begin event
    function handleSessionSetBegin(value) {
        const sessId = parseBigEndian(value, 2, 4);
        elements.sessionStatus.textContent = 'Sesja aktywna - start!';
        console.log('Session set begin:', { sessId });
    }

    // Send command
    async function sendCommand(cmdId) {
        try {
            const data = new Uint8Array([0x01, cmdId]);
            await characteristics.command.writeValue(data);
            console.log('Command sent:', cmdId);
        } catch (error) {
            console.error('Error sending command:', error);
            alert('Blad wysylania komendy: ' + error.message);
        }
    }

    // Load saved sessions
    async function loadSessions() {
        elements.sessionsLoading.classList.remove('hidden');
        elements.sessionList.innerHTML = '';
        elements.noSessions.classList.add('hidden');
        elements.shotsCard.classList.add('hidden');

        try {
            const sessions = [];

            // Write 0xFFFFFFFF to start from newest
            const startValue = new Uint8Array([0xFF, 0xFF, 0xFF, 0xFF]);
            await characteristics.sessionList.writeValue(startValue);

            // Read sessions
            let endReached = false;
            while (!endReached && sessions.length < 100) {
                const value = await characteristics.sessionList.readValue();
                const sessId = parseBigEndian(value, 0, 4);

                if (sessId === 0xFFFFFFFF) {
                    endReached = true;
                } else {
                    sessions.push(sessId);
                }
            }

            elements.sessionsLoading.classList.add('hidden');

            if (sessions.length === 0) {
                elements.noSessions.classList.remove('hidden');
                return;
            }

            // Display sessions
            sessions.forEach(sessId => {
                const li = document.createElement('li');
                li.className = 'session-item';
                li.innerHTML = `
                    <div>
                        <div class="session-date">${formatDate(sessId)}</div>
                        <div class="session-id">ID: ${sessId}</div>
                    </div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                `;
                li.addEventListener('click', () => loadSessionShots(sessId, li));
                elements.sessionList.appendChild(li);
            });

        } catch (error) {
            console.error('Error loading sessions:', error);
            elements.sessionsLoading.classList.add('hidden');
            alert('Blad ladowania sesji: ' + error.message);
        }
    }

    // Load shots for a session
    async function loadSessionShots(sessId, listItem) {
        // Update active state
        document.querySelectorAll('.session-item').forEach(item => item.classList.remove('active'));
        listItem.classList.add('active');

        elements.selectedSessionDate.textContent = formatDate(sessId);
        elements.shotsCard.classList.remove('hidden');
        elements.shotsLoading.classList.remove('hidden');
        elements.shotsTable.classList.add('hidden');
        elements.noShots.classList.add('hidden');
        elements.shotsBody.innerHTML = '';

        try {
            // Write session ID to shot list characteristic
            const sessIdBytes = new Uint8Array([
                (sessId >> 24) & 0xFF,
                (sessId >> 16) & 0xFF,
                (sessId >> 8) & 0xFF,
                sessId & 0xFF
            ]);
            await characteristics.shotList.writeValue(sessIdBytes);

            const shots = [];
            let endReached = false;

            while (!endReached && shots.length < 1000) {
                const value = await characteristics.shotList.readValue();
                const shotNum = parseBigEndian(value, 0, 2);
                const shotTime = parseBigEndian(value, 2, 4);

                if (shotTime === 0xFFFFFFFF) {
                    endReached = true;
                } else {
                    shots.push({ num: shotNum + 1, time: shotTime });
                }
            }

            elements.shotsLoading.classList.add('hidden');

            if (shots.length === 0) {
                elements.noShots.classList.remove('hidden');
                return;
            }

            elements.shotsTable.classList.remove('hidden');

            // Calculate splits and display
            let prevTime = 0;
            shots.forEach((shot, index) => {
                const split = index === 0 ? shot.time : shot.time - prevTime;
                prevTime = shot.time;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="shot-num">${shot.num}</td>
                    <td class="shot-time">${formatTime(shot.time)}s</td>
                    <td class="split-time">${index === 0 ? '-' : '+' + formatTime(split) + 's'}</td>
                `;
                elements.shotsBody.appendChild(row);
            });

        } catch (error) {
            console.error('Error loading shots:', error);
            elements.shotsLoading.classList.add('hidden');
            alert('Blad ladowania strzalow: ' + error.message);
        }
    }

    // Event listeners
    elements.btnConnect.addEventListener('click', connect);
    elements.btnDisconnect.addEventListener('click', disconnect);
    elements.btnLoadSessions.addEventListener('click', loadSessions);
    elements.btnStart.addEventListener('click', () => sendCommand(CMD_SESSION_START));
    elements.btnStop.addEventListener('click', () => sendCommand(CMD_SESSION_STOP));

    // Initialize
    checkBrowserSupport();
    </script>
</body>
</html>
