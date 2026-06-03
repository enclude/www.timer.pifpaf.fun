<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odczyt sesji strzeleckiej</title>
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

        .btn-small {
            display: flex;
            width: fit-content;
            padding: 6px 12px;
            font-size: 0.85rem;
            margin-top: 8px;
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

        .calc-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .field-group label {
            display: block;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .field-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            color: var(--text-primary);
            background: white;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .field-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--focus-glow);
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

        .session-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 2px;
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
                    <button id="btnSyncTime" class="btn btn-outline btn-small">Synchronizuj czas</button>
                </div>
            </div>
        </div>

        <!-- Calculator data: stage name + participant (sent as GET params) -->
        <div id="calcDataCard" class="card hidden">
            <h2>Dane do kalkulatora</h2>
            <div class="calc-fields">
                <div class="field-group">
                    <label for="inputNazwaToru">Nazwa toru</label>
                    <input type="text" id="inputNazwaToru" placeholder="np. Tor 1">
                </div>
                <div class="field-group">
                    <label for="inputUczestnik">Uczestnik</label>
                    <input type="text" id="inputUczestnik" placeholder="np. Jan Kowalski">
                </div>
            </div>
        </div>

        <!-- Live Session Display -->
        <div id="liveSection" class="hidden">
            <div class="live-display">
                <div id="sessionStatus" class="session-status">Oczekiwanie na sesje...</div>
                <div id="currentTime" class="current-time">0.00</div>
                <div id="shotCount" class="shot-count">Strzaly: 0</div>
            </div>

            <div class="controls">
                <button id="btnStart" class="btn btn-accent">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    Start
                </button>
                <button id="btnStartPar" class="btn btn-accent">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    Start z opoznieniem
                </button>
                <button id="btnStop" class="btn btn-danger" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="6" y="6" width="12" height="12"/>
                    </svg>
                    Stop
                </button>
                <button id="btnSendToCalc" class="btn btn-outline hidden">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2"/>
                        <line x1="8" y1="7" x2="16" y2="7"/>
                        <line x1="8" y1="11" x2="10" y2="11"/>
                        <line x1="8" y1="15" x2="10" y2="15"/>
                        <line x1="14" y1="11" x2="16" y2="11"/>
                        <line x1="14" y1="15" x2="16" y2="15"/>
                    </svg>
                    Wyslij do kalkulatora
                </button>
            </div>
        </div>

        <!-- Saved Sessions -->
        <div id="sessionsCard" class="card hidden">
            <h2>Zapisane sesje</h2>

            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                <button id="btnLoadSessions" class="btn btn-outline">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 4v6h-6M1 20v-6h6"/>
                        <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                    </svg>
                    Odswiezaj liste sesji
                </button>

                <button id="btnCacheSessions" class="btn btn-outline">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Pobierz sesje do cache
                </button>
            </div>

            <div id="sessionsLoading" class="loading hidden">
                <div class="spinner"></div>
                Ladowanie sesji...
            </div>

            <div id="cacheLoading" class="loading hidden">
                <div class="spinner"></div>
                <span id="cacheProgress">Pobieranie sesji do cache...</span>
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

        <!-- Cached Sessions (browsable without BLE connection) -->
        <div id="cacheCard" class="card hidden">
            <h2>Sesje z cache (ostatnie 24h)</h2>

            <p id="cacheInfo" style="margin-bottom: 15px; font-size: 0.85rem; color: var(--text-secondary);"></p>

            <ul id="cacheList" class="session-list"></ul>

            <button id="btnClearCache" class="btn btn-outline" style="margin-top: 15px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                </svg>
                Wyczysc cache
            </button>
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

            <div style="margin-top: 15px; text-align: right;">
                <button id="btnSendHistoryToCalc" class="btn btn-outline hidden">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2"/>
                        <line x1="8" y1="7" x2="16" y2="7"/>
                        <line x1="8" y1="11" x2="10" y2="11"/>
                        <line x1="8" y1="15" x2="10" y2="15"/>
                        <line x1="14" y1="11" x2="16" y2="11"/>
                        <line x1="14" y1="15" x2="16" y2="15"/>
                    </svg>
                    Wyslij do kalkulatora
                </button>
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
        <?php
        if (file_exists(__DIR__ . '/version.php')) {
            include_once __DIR__ . '/version.php';
            if (defined('APP_COMMIT_HASH') && APP_COMMIT_HASH !== 'dev') {
                $date = APP_COMMIT_DATE
                    ? date('d.m.Y H:i', strtotime(APP_COMMIT_DATE))
                    : '';
                echo '<p style="margin-top: 4px; font-size: 0.75rem; opacity: 0.6;">';
                echo 'wersja: <a href="' . APP_COMMIT_URL . '" target="_blank" style="color:inherit;">' . APP_COMMIT_HASH . '</a>';
                if ($date) echo ' &middot; ' . $date;
                echo '</p>';
            }
        }
        ?>
    </footer>

    <script>
    // BLE UUIDs
    const BLE_SERVICE_UUID = '7520ffff-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_COMMAND = '75200000-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_EVENT = '75200001-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_SESSION_LIST = '75200002-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_SHOT_LIST = '75200004-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_UNIX_TIME = '75200006-14d2-4cda-8b6b-697c554c9311';
    const BLE_CHAR_PAR_SETUP = '75200005-14d2-4cda-8b6b-697c554c9311';
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
    let historySession = { sessId: null, shots: [] };
    let metadataAborted = false;
    let metadataTask = Promise.resolve();
    let shotsLoadToken = 0;

    // Web Bluetooth allows only ONE outstanding GATT operation per device.
    // Concurrent readValue/writeValue calls fail with "GATT operation failed
    // for unknown reason", so every GATT operation goes through this queue.
    let gattChain = Promise.resolve();
    function gattExec(operation) {
        const result = gattChain.then(operation, operation);
        gattChain = result.then(() => {}, () => {});
        return result;
    }

    // Stop the background metadata loader and WAIT until its in-flight
    // GATT operation finishes, so a new session/shot read sequence
    // does not interleave with it on the same characteristic cursor.
    async function stopMetadataLoad() {
        metadataAborted = true;
        await metadataTask.catch(() => {});
    }

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
        btnSyncTime: document.getElementById('btnSyncTime'),
        liveSection: document.getElementById('liveSection'),
        sessionStatus: document.getElementById('sessionStatus'),
        currentTime: document.getElementById('currentTime'),
        shotCount: document.getElementById('shotCount'),
        btnStart: document.getElementById('btnStart'),
        btnStop: document.getElementById('btnStop'),
        sessionsCard: document.getElementById('sessionsCard'),
        btnLoadSessions: document.getElementById('btnLoadSessions'),
        btnCacheSessions: document.getElementById('btnCacheSessions'),
        sessionsLoading: document.getElementById('sessionsLoading'),
        cacheLoading: document.getElementById('cacheLoading'),
        cacheProgress: document.getElementById('cacheProgress'),
        cacheCard: document.getElementById('cacheCard'),
        cacheInfo: document.getElementById('cacheInfo'),
        cacheList: document.getElementById('cacheList'),
        btnClearCache: document.getElementById('btnClearCache'),
        sessionList: document.getElementById('sessionList'),
        noSessions: document.getElementById('noSessions'),
        shotsCard: document.getElementById('shotsCard'),
        selectedSessionDate: document.getElementById('selectedSessionDate'),
        shotsLoading: document.getElementById('shotsLoading'),
        shotsTable: document.getElementById('shotsTable'),
        shotsBody: document.getElementById('shotsBody'),
        noShots: document.getElementById('noShots'),
        liveShotsCard: document.getElementById('liveShotsCard'),
        liveShotsBody: document.getElementById('liveShotsBody'),
        btnSendToCalc: document.getElementById('btnSendToCalc'),
        btnStartPar: document.getElementById('btnStartPar'),
        btnSendHistoryToCalc: document.getElementById('btnSendHistoryToCalc'),
        calcDataCard: document.getElementById('calcDataCard'),
        inputNazwaToru: document.getElementById('inputNazwaToru'),
        inputUczestnik: document.getElementById('inputUczestnik')
    };

    // Persist stage name per browser (localStorage)
    const STORAGE_KEY_NAZWA_TORU = 'sgtimer_nazwa_toru';

    function loadNazwaToru() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY_NAZWA_TORU);
            if (saved !== null) {
                elements.inputNazwaToru.value = saved;
            }
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
    }

    function saveNazwaToru() {
        try {
            localStorage.setItem(STORAGE_KEY_NAZWA_TORU, elements.inputNazwaToru.value);
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
    }

    // Session cache (localStorage) — sessions from the last 24h with full
    // shot lists, browsable without a BLE connection
    const STORAGE_KEY_SESSION_CACHE = 'sgtimer_session_cache';
    const CACHE_MAX_AGE_S = 24 * 3600;

    function readSessionCache() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY_SESSION_CACHE);
            if (!raw) return null;
            const cache = JSON.parse(raw);
            if (!cache || !Array.isArray(cache.sessions)) return null;
            return cache;
        } catch (e) {
            console.warn('Session cache read error:', e);
            return null;
        }
    }

    function writeSessionCache(cache) {
        try {
            localStorage.setItem(STORAGE_KEY_SESSION_CACHE, JSON.stringify(cache));
            return true;
        } catch (e) {
            console.error('Session cache write error:', e);
            alert('Blad zapisu cache: ' + e.message);
            return false;
        }
    }

    function clearSessionCache() {
        try {
            localStorage.removeItem(STORAGE_KEY_SESSION_CACHE);
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
        renderCacheCard();
    }

    // Render the cached sessions card — works without a BLE connection
    function renderCacheCard() {
        const cache = readSessionCache();
        elements.cacheList.innerHTML = '';

        if (!cache || cache.sessions.length === 0) {
            elements.cacheCard.classList.add('hidden');
            return;
        }

        elements.cacheCard.classList.remove('hidden');
        elements.cacheInfo.textContent =
            `Pobrano: ${formatDate(cache.savedAt)} · sesji: ${cache.sessions.length}`;

        cache.sessions.forEach(({ sessId, shots }) => {
            const li = document.createElement('li');
            li.className = 'session-item';
            const lastShot = shots[shots.length - 1];
            const meta = shots.length === 0
                ? 'Brak strzałów'
                : `${shots.length} strzałów · ${formatTime(lastShot.time)}s`;
            li.innerHTML = `
                <div>
                    <div class="session-date">${formatDate(sessId)}</div>
                    <div class="session-meta">${meta}</div>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            `;
            li.addEventListener('click', () => {
                // Supersede any in-flight BLE shot load so it does not
                // overwrite this instantly-rendered cached view
                shotsLoadToken++;
                document.querySelectorAll('.session-item').forEach(item => item.classList.remove('active'));
                li.classList.add('active');
                renderShots(sessId, shots);
            });
            elements.cacheList.appendChild(li);
        });
    }

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
        return seconds.toFixed(2);
    }

    // Format Unix timestamp to date
    // Device stores local time as Unix timestamp (no UTC offset) — use timeZone:'UTC' to avoid double offset
    function formatDate(timestamp) {
        const date = new Date(timestamp * 1000);
        return date.toLocaleString('pl-PL', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            timeZone: 'UTC'
        });
    }

    // Parse Big Endian bytes to number (unsigned 32-bit safe)
    // >>> 0 converts JS signed 32-bit result back to unsigned — required for 0xFFFFFFFF sentinel detection
    function parseBigEndian(dataView, offset, bytes) {
        let value = 0;
        for (let i = 0; i < bytes; i++) {
            value = (value << 8) | dataView.getUint8(offset + i);
        }
        return value >>> 0;
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

            // PAR_SETUP is optional — UUID may vary by firmware; disable button if not found
            try {
                characteristics.parSetup = await service.getCharacteristic(BLE_CHAR_PAR_SETUP);
            } catch (e) {
                console.warn('PAR_SETUP characteristic not found:', BLE_CHAR_PAR_SETUP);
                characteristics.parSetup = null;
                elements.btnStartPar.disabled = true;
                elements.btnStartPar.title = 'PAR_SETUP niedostepny na tym urzadzeniu';
            }

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
            elements.calcDataCard.classList.remove('hidden');
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
        elements.calcDataCard.classList.add('hidden');
        elements.liveSection.classList.add('hidden');
        elements.sessionsCard.classList.add('hidden');
        elements.shotsCard.classList.add('hidden');
        elements.liveShotsCard.classList.add('hidden');

        device = null;
        server = null;
        service = null;
        characteristics = {};
        currentSession = { id: null, active: false, shots: [], lastShotTime: 0 };

        // Stop background loaders — characteristics are gone
        metadataAborted = true;
        shotsLoadToken++;

        console.log('Disconnected');
    }

    // Read API version
    async function readApiVersion() {
        try {
            const value = await gattExec(() => characteristics.apiVersion.readValue());
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
            const value = await gattExec(() => characteristics.unixTime.readValue());
            const timestamp = parseBigEndian(value, 0, 4);
            elements.deviceTime.textContent = formatDate(timestamp);
        } catch (error) {
            console.error('Error reading device time:', error);
            elements.deviceTime.textContent = 'Blad odczytu';
        }
    }

    // Sync device time with browser clock
    // Device stores LOCAL time as Unix timestamp (no UTC offset) — write local epoch, not UTC
    async function syncDeviceTime() {
        elements.btnSyncTime.disabled = true;
        try {
            const localTimestamp = Math.floor(Date.now() / 1000) - new Date().getTimezoneOffset() * 60;
            const timeBytes = new Uint8Array([
                (localTimestamp >> 24) & 0xFF,
                (localTimestamp >> 16) & 0xFF,
                (localTimestamp >> 8) & 0xFF,
                localTimestamp & 0xFF
            ]);
            await gattExec(() => characteristics.unixTime.writeValue(timeBytes));
            console.log('Device time synced:', localTimestamp);
            await readDeviceTime();
        } catch (error) {
            console.error('Error syncing device time:', error);
            alert('Blad synchronizacji czasu: ' + error.message);
        } finally {
            elements.btnSyncTime.disabled = false;
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
        elements.currentTime.textContent = '0.00';
        elements.shotCount.textContent = 'Strzaly: 0';
        elements.btnStart.disabled = true;
        elements.btnStartPar.disabled = true;
        elements.btnStop.disabled = false;
        elements.btnSendToCalc.classList.add('hidden');
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
        elements.btnStartPar.disabled = false;
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
        elements.btnStartPar.disabled = true;
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
        elements.btnStartPar.disabled = false;
        elements.btnStop.disabled = true;
        if (totalShots > 0) {
            elements.btnSendToCalc.classList.remove('hidden');
        }

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

        currentSession.shots.push({ num: shotNum + 1, time: shotTime, split: split });
        currentSession.lastShotTime = shotTime;

        // Update live display
        elements.currentTime.textContent = formatTime(shotTime);
        elements.shotCount.textContent = `Strzaly: ${shotNum + 1}`;

        // Add to live shots table
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="shot-num">${shotNum + 1}</td>
            <td class="shot-time">${formatTime(shotTime)}s</td>
            <td class="split-time">${shotNum === 0 ? '-' : '+' + formatTime(split) + 's'}</td>
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

    // Start session immediately (zero delay) via PAR_SETUP (API 1.5)
    // start_delay=0x0000 = immediate start, time_limit=0 (unlimited), shot_limit=0 (unlimited)
    async function startNow() {
        if (characteristics.parSetup) {
            try {
                const parData = new Uint8Array([0x00, 0x00, 0x00, 0x00, 0x00, 0x00]);
                await gattExec(() => characteristics.parSetup.writeValue(parData));
            } catch (error) {
                console.warn('PAR_SETUP write failed, starting without reset:', error);
            }
        }
        await sendCommand(CMD_SESSION_START);
    }

    // Start session with random delay 1.0–4.0s via PAR_SETUP (API 1.5)
    // start_delay=0xFFFF triggers random delay, time_limit=0 (unlimited), shot_limit=0 (unlimited)
    async function startWithRandomDelay() {
        if (!characteristics.parSetup) {
            alert('PAR_SETUP niedostepny — sprawdz konsole po poprawny UUID charakterystyki.');
            return;
        }
        try {
            const parData = new Uint8Array([0xFF, 0xFF, 0x00, 0x00, 0x00, 0x00]);
            console.log('Writing PAR_SETUP to UUID:', BLE_CHAR_PAR_SETUP, parData);
            await gattExec(() => characteristics.parSetup.writeValue(parData));
            await sendCommand(CMD_SESSION_START);
        } catch (error) {
            console.error('PAR_SETUP write failed (UUID may be wrong):', BLE_CHAR_PAR_SETUP, error);
            alert('Blad startu z opoznieniem: ' + error.message);
        }
    }

    // Send command
    async function sendCommand(cmdId) {
        try {
            const data = new Uint8Array([0x01, cmdId]);
            await gattExec(() => characteristics.command.writeValue(data));
            console.log('Command sent:', cmdId);
        } catch (error) {
            console.error('Error sending command:', error);
            alert('Blad wysylania komendy: ' + error.message);
        }
    }

    // Load saved sessions
    async function loadSessions() {
        // Stop background metadata loading from a previous run before
        // resetting the sessionList read cursor; supersede any running
        // shot load — its list item is about to be removed
        shotsLoadToken++;
        await stopMetadataLoad();

        elements.btnLoadSessions.disabled = true;
        elements.btnCacheSessions.disabled = true;
        elements.sessionsLoading.classList.remove('hidden');
        elements.sessionList.innerHTML = '';
        elements.noSessions.classList.add('hidden');
        elements.shotsCard.classList.add('hidden');

        try {
            const sessions = [];

            // Write 0xFFFFFFFF to start from newest
            const startValue = new Uint8Array([0xFF, 0xFF, 0xFF, 0xFF]);
            await gattExec(() => characteristics.sessionList.writeValue(startValue));

            // Read sessions
            let endReached = false;
            while (!endReached && sessions.length < 100) {
                const value = await gattExec(() => characteristics.sessionList.readValue());
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
                li.dataset.sessId = sessId;
                li.innerHTML = `
                    <div>
                        <div class="session-date">${formatDate(sessId)}</div>
                        <div class="session-meta">ładowanie...</div>
                    </div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                `;
                li.addEventListener('click', () => loadSessionShots(sessId, li));
                elements.sessionList.appendChild(li);
            });

            // Load shot count and duration for each session in background
            metadataAborted = false;
            metadataTask = loadSessionsMetadata(sessions);

        } catch (error) {
            console.error('Error loading sessions:', error);
            elements.sessionsLoading.classList.add('hidden');
            alert('Blad ladowania sesji: ' + error.message);
        } finally {
            elements.btnLoadSessions.disabled = false;
            elements.btnCacheSessions.disabled = false;
        }
    }

    // Load shot count and duration for each session (runs in background)
    async function loadSessionsMetadata(sessions) {
        for (const sessId of sessions) {
            if (metadataAborted) break;

            try {
                const sessIdBytes = new Uint8Array([
                    (sessId >> 24) & 0xFF,
                    (sessId >> 16) & 0xFF,
                    (sessId >> 8) & 0xFF,
                    sessId & 0xFF
                ]);
                await gattExec(() => characteristics.shotList.writeValue(sessIdBytes));

                let shotCount = 0;
                let lastShotTime = 0;
                let endReached = false;

                while (!endReached && shotCount < 1000) {
                    if (metadataAborted) break;
                    const value = await gattExec(() => characteristics.shotList.readValue());
                    const shotTime = parseBigEndian(value, 2, 4);
                    if (shotTime === 0xFFFFFFFF) {
                        endReached = true;
                    } else {
                        shotCount++;
                        lastShotTime = shotTime;
                    }
                }

                if (!metadataAborted) {
                    const li = elements.sessionList.querySelector(`[data-sess-id="${sessId}"]`);
                    const metaEl = li ? li.querySelector('.session-meta') : null;
                    if (metaEl) {
                        metaEl.textContent = shotCount === 0
                            ? 'Brak strzałów'
                            : `${shotCount} strzałów · ${formatTime(lastShotTime)}s`;
                    }
                }
            } catch (error) {
                console.warn('Metadata load error for session', sessId, error);
            }
        }
    }

    // Download sessions from the last 24h (with full shot lists) into
    // localStorage so they can be browsed without occupying the timer via BLE
    async function downloadSessionsToCache() {
        // Supersede any running shot load and stop the background metadata
        // loader before resetting the sessionList/shotList read cursors
        const token = ++shotsLoadToken;
        await stopMetadataLoad();
        if (token !== shotsLoadToken) return;

        elements.btnCacheSessions.disabled = true;
        elements.btnLoadSessions.disabled = true;
        elements.cacheLoading.classList.remove('hidden');
        elements.cacheProgress.textContent = 'Pobieranie listy sesji...';

        try {
            // Device time uses the same local-time-as-timestamp convention
            // as session IDs, so the 24h cutoff is computed from device time
            const timeValue = await gattExec(() => characteristics.unixTime.readValue());
            const deviceNow = parseBigEndian(timeValue, 0, 4);
            const cutoff = deviceNow - CACHE_MAX_AGE_S;

            // Write 0xFFFFFFFF to start from newest
            const startValue = new Uint8Array([0xFF, 0xFF, 0xFF, 0xFF]);
            await gattExec(() => characteristics.sessionList.writeValue(startValue));

            // Read session IDs newest-to-oldest; stop early below the cutoff
            const sessionIds = [];
            let endReached = false;
            while (!endReached && sessionIds.length < 100) {
                if (token !== shotsLoadToken) return;
                const value = await gattExec(() => characteristics.sessionList.readValue());
                const sessId = parseBigEndian(value, 0, 4);

                if (sessId === 0xFFFFFFFF || sessId < cutoff) {
                    endReached = true;
                } else {
                    sessionIds.push(sessId);
                }
            }

            if (sessionIds.length === 0) {
                alert('Brak sesji z ostatnich 24h na timerze.');
                return;
            }

            // Read the full shot list for each session
            const sessions = [];
            for (let i = 0; i < sessionIds.length; i++) {
                const sessId = sessionIds[i];
                elements.cacheProgress.textContent =
                    `Pobieranie sesji ${i + 1}/${sessionIds.length}...`;

                const sessIdBytes = new Uint8Array([
                    (sessId >> 24) & 0xFF,
                    (sessId >> 16) & 0xFF,
                    (sessId >> 8) & 0xFF,
                    sessId & 0xFF
                ]);
                await gattExec(() => characteristics.shotList.writeValue(sessIdBytes));

                const shots = [];
                let shotsEnd = false;
                while (!shotsEnd && shots.length < 1000) {
                    if (token !== shotsLoadToken) return;
                    const value = await gattExec(() => characteristics.shotList.readValue());
                    const shotNum = parseBigEndian(value, 0, 2);
                    const shotTime = parseBigEndian(value, 2, 4);

                    if (shotTime === 0xFFFFFFFF) {
                        shotsEnd = true;
                    } else {
                        shots.push({ num: shotNum + 1, time: shotTime });
                    }
                }

                sessions.push({ sessId, shots });
            }

            if (writeSessionCache({ savedAt: deviceNow, sessions })) {
                renderCacheCard();
            }

        } catch (error) {
            console.error('Error caching sessions:', error);
            alert('Blad pobierania sesji do cache: ' + error.message);
        } finally {
            elements.cacheLoading.classList.add('hidden');
            elements.btnCacheSessions.disabled = false;
            elements.btnLoadSessions.disabled = false;
        }
    }

    // Load shots for a session
    async function loadSessionShots(sessId, listItem) {
        // Supersede any previous shot load and stop the background
        // metadata loader before resetting the shotList read cursor
        const token = ++shotsLoadToken;
        await stopMetadataLoad();
        if (token !== shotsLoadToken) return;

        // Update active state
        document.querySelectorAll('.session-item').forEach(item => item.classList.remove('active'));
        listItem.classList.add('active');

        elements.selectedSessionDate.textContent = formatDate(sessId);
        elements.shotsCard.classList.remove('hidden');
        elements.shotsLoading.classList.remove('hidden');
        elements.shotsTable.classList.add('hidden');
        elements.noShots.classList.add('hidden');
        elements.btnSendHistoryToCalc.classList.add('hidden');
        elements.shotsBody.innerHTML = '';

        try {
            // Write session ID to shot list characteristic
            const sessIdBytes = new Uint8Array([
                (sessId >> 24) & 0xFF,
                (sessId >> 16) & 0xFF,
                (sessId >> 8) & 0xFF,
                sessId & 0xFF
            ]);
            await gattExec(() => characteristics.shotList.writeValue(sessIdBytes));

            const shots = [];
            let endReached = false;

            while (!endReached && shots.length < 1000) {
                // A newer shot load has reset the shotList cursor — abandon this one
                if (token !== shotsLoadToken) return;
                const value = await gattExec(() => characteristics.shotList.readValue());
                const shotNum = parseBigEndian(value, 0, 2);
                const shotTime = parseBigEndian(value, 2, 4);

                if (shotTime === 0xFFFFFFFF) {
                    endReached = true;
                } else {
                    shots.push({ num: shotNum + 1, time: shotTime });
                }
            }

            if (token !== shotsLoadToken) return;
            renderShots(sessId, shots);

        } catch (error) {
            console.error('Error loading shots:', error);
            elements.shotsLoading.classList.add('hidden');
            alert('Blad ladowania strzalow: ' + error.message);
        }
    }

    // Render the shot table for a session (shared by BLE load and cache view)
    function renderShots(sessId, shots) {
        elements.selectedSessionDate.textContent = formatDate(sessId);
        elements.shotsCard.classList.remove('hidden');
        elements.shotsLoading.classList.add('hidden');
        elements.shotsBody.innerHTML = '';

        if (shots.length === 0) {
            elements.shotsTable.classList.add('hidden');
            elements.noShots.classList.remove('hidden');
            elements.btnSendHistoryToCalc.classList.add('hidden');
            return;
        }

        elements.noShots.classList.add('hidden');
        elements.shotsTable.classList.remove('hidden');

        // Store for calculator export
        historySession = { sessId, shots };
        elements.btnSendHistoryToCalc.classList.remove('hidden');

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
    }

    // Send historical session data to external calculator (includes session date in opis)
    function sendHistoryToCalculator() {
        const { sessId, shots } = historySession;
        if (!shots || shots.length === 0) return;

        const lastShot = shots[shots.length - 1];
        const liczbaStrzalow = shots.length;
        const czasBazowy = formatTime(lastShot.time);

        const sessionDate = formatDate(sessId);
        let prevTime = 0;
        const opisParts = shots.map((shot, index) => {
            const split = index === 0 ? shot.time : shot.time - prevTime;
            prevTime = shot.time;
            const splitStr = index === 0 ? '' : ` (+${formatTime(split)}s)`;
            return `${shot.num}: ${formatTime(shot.time)}s${splitStr}`;
        });
        const opis = sessionDate + ' | ' + opisParts.join(' | ');

        const params = new URLSearchParams({
            liczba_strzalow: liczbaStrzalow,
            czas_bazowy: czasBazowy,
            opis: opis
        });
        appendCalcDataParams(params);
        window.open(`https://piro-kalkulator.pifpaf.fun/?${params.toString()}`, '_blank');
    }

    // Send session data to external calculator
    function sendToCalculator() {
        const shots = currentSession.shots;
        if (shots.length === 0) return;

        const lastShot = shots[shots.length - 1];
        const liczbaStrzalow = shots.length;
        const czasBazowy = formatTime(lastShot.time);

        const opisParts = shots.map(shot => {
            const splitStr = shot.num === 1 ? '' : ` (+${formatTime(shot.split)}s)`;
            return `${shot.num}: ${formatTime(shot.time)}s${splitStr}`;
        });
        const opis = opisParts.join(' | ');

        const params = new URLSearchParams({
            liczba_strzalow: liczbaStrzalow,
            czas_bazowy: czasBazowy,
            opis: opis
        });
        appendCalcDataParams(params);
        window.open(`https://piro-kalkulator.pifpaf.fun/?${params.toString()}`, '_blank');
    }

    // Append stage name and participant to calculator URL params (if filled in)
    function appendCalcDataParams(params) {
        const nazwaToru = elements.inputNazwaToru.value.trim();
        const uczestnik = elements.inputUczestnik.value.trim();
        if (nazwaToru) params.set('nazwa_toru', nazwaToru);
        if (uczestnik) params.set('uczestnik', uczestnik);
    }

    // Event listeners
    elements.btnConnect.addEventListener('click', connect);
    elements.btnDisconnect.addEventListener('click', disconnect);
    elements.btnLoadSessions.addEventListener('click', loadSessions);
    elements.btnCacheSessions.addEventListener('click', downloadSessionsToCache);
    elements.btnClearCache.addEventListener('click', clearSessionCache);
    elements.btnSyncTime.addEventListener('click', syncDeviceTime);
    elements.btnStart.addEventListener('click', startNow);
    elements.btnStartPar.addEventListener('click', startWithRandomDelay);
    elements.btnStop.addEventListener('click', () => sendCommand(CMD_SESSION_STOP));
    elements.btnSendToCalc.addEventListener('click', sendToCalculator);
    elements.btnSendHistoryToCalc.addEventListener('click', sendHistoryToCalculator);
    elements.inputNazwaToru.addEventListener('input', saveNazwaToru);

    // Initialize
    checkBrowserSupport();
    loadNazwaToru();
    renderCacheCard();
    </script>
</body>
</html>
