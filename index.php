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

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            font-size: 0.85rem;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .checkbox-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .btn-id-tone-replay {
            margin: 6px 0 0 auto;
        }

        .btn-db-edit-link {
            margin: 6px 0 0 8px;
            text-decoration: none;
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

        .db-save-status {
            font-size: 0.85rem;
            color: var(--accent);
            font-weight: 500;
        }

        .db-save-status.error {
            color: var(--alert-red);
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
                    <label>Nazwa urzadzenia (nr seryjny)</label>
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
            <label class="checkbox-row" for="inputPlayIdTone">
                <input type="checkbox" id="inputPlayIdTone">
                Zagraj sygnał ID po zapisie (do synchronizacji z kamerą)
            </label>
        </div>

        <!-- PAR settings: time limit + shot limit written to the timer (PAR_SETUP) -->
        <div id="parCard" class="card hidden">
            <h2>Ustawienia PAR</h2>
            <div class="calc-fields">
                <div class="field-group">
                    <label for="inputParTime">Czas maksymalny [s] (0 = bez limitu)</label>
                    <input type="number" id="inputParTime" min="0" max="6553.4" step="0.1" value="0">
                </div>
                <div class="field-group">
                    <label for="inputParShots">Limit strzalow (0 = bez limitu)</label>
                    <input type="number" id="inputParShots" min="0" max="65534" step="1" value="0">
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 12px;">
                <button id="btnWritePar" class="btn btn-outline">Zapisz PAR w timerze</button>
                <button id="btnResetPar" class="btn btn-outline">Zresetuj PAR</button>
                <span id="parStatus" style="font-size: 0.85rem; color: var(--text-secondary);"></span>
            </div>
            <p style="margin-top: 10px; font-size: 0.8rem; color: var(--text-secondary);">
                Limity sa zapisywane do timera przyciskiem powyzej oraz automatycznie przy kazdym
                starcie z tej strony. Timer zakonczy sesje po osiagnieciu limitu czasu lub strzalow.
            </p>
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
                <button id="btnSaveToDb" class="btn btn-outline hidden">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                        <path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>
                        <path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/>
                    </svg>
                    Zapisz w bazie
                </button>
            </div>
            <div id="dbSaveStatusLive" class="db-save-status hidden" style="margin-top: 8px; text-align: right;"></div>
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

            <div style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; align-items: center;">
                <span id="dbSaveStatusHistory" class="db-save-status hidden"></span>
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
                <button id="btnSaveHistoryToDb" class="btn btn-outline hidden">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                        <path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>
                        <path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/>
                    </svg>
                    Zapisz w bazie
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
        // Version read straight from .git (server is deployed via cron git pull),
        // so the footer always shows the commit that is actually live.
        function appVersion() {
            $gitDir = __DIR__ . '/.git';
            $head = @file_get_contents($gitDir . '/HEAD');
            if ($head === false) return null;
            $head = trim($head);
            $hash = '';
            $deployedAt = false;
            if (strpos($head, 'ref:') === 0) {
                $refName = trim(substr($head, 4));
                $refPath = $gitDir . '/' . $refName;
                $ref = @file_get_contents($refPath);
                if ($ref !== false) {
                    $hash = trim($ref);
                    $deployedAt = @filemtime($refPath);
                } else {
                    // Ref may be packed (after git gc)
                    $packed = @file_get_contents($gitDir . '/packed-refs');
                    if ($packed !== false
                        && preg_match('/^([0-9a-f]{40})\s+' . preg_quote($refName, '/') . '$/m', $packed, $m)) {
                        $hash = $m[1];
                    }
                }
            } else {
                $hash = $head; // detached HEAD
                $deployedAt = @filemtime($gitDir . '/HEAD');
            }
            if (!preg_match('/^[0-9a-f]{40}$/', $hash)) return null;
            return ['hash' => $hash, 'deployedAt' => $deployedAt];
        }
        $ver = appVersion();
        if ($ver) {
            echo '<p style="margin-top: 4px; font-size: 0.75rem; opacity: 0.6;">';
            echo 'wersja: <a href="https://github.com/enclude/www.timer.pifpaf.fun/commit/' . $ver['hash']
                . '" target="_blank" style="color:inherit;">' . substr($ver['hash'], 0, 7) . '</a>';
            if ($ver['deployedAt']) {
                echo ' &middot; wdro&#380;ono ' . date('d.m.Y H:i', $ver['deployedAt']);
            }
            echo '</p>';
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
    // BLE device name doubles as the timer serial number (e.g. SG-SST4B00000);
    // kept across disconnects so cache-based saves can still attribute the device
    let deviceSerial = '';
    // PAR limits currently stored ON THE DEVICE (from last read/write this
    // connection) — the inputs may differ until the next write
    let lastWrittenPar = null;
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
        btnSaveToDb: document.getElementById('btnSaveToDb'),
        dbSaveStatusLive: document.getElementById('dbSaveStatusLive'),
        btnStartPar: document.getElementById('btnStartPar'),
        btnSendHistoryToCalc: document.getElementById('btnSendHistoryToCalc'),
        btnSaveHistoryToDb: document.getElementById('btnSaveHistoryToDb'),
        dbSaveStatusHistory: document.getElementById('dbSaveStatusHistory'),
        calcDataCard: document.getElementById('calcDataCard'),
        inputNazwaToru: document.getElementById('inputNazwaToru'),
        inputUczestnik: document.getElementById('inputUczestnik'),
        inputPlayIdTone: document.getElementById('inputPlayIdTone'),
        parCard: document.getElementById('parCard'),
        inputParTime: document.getElementById('inputParTime'),
        inputParShots: document.getElementById('inputParShots'),
        btnWritePar: document.getElementById('btnWritePar'),
        btnResetPar: document.getElementById('btnResetPar'),
        parStatus: document.getElementById('parStatus')
    };

    // Persist stage name per browser (localStorage), expires 8h after last use
    const STORAGE_KEY_NAZWA_TORU = 'sgtimer_nazwa_toru';
    const NAZWA_TORU_TTL_MS = 8 * 60 * 60 * 1000;

    function loadNazwaToru() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY_NAZWA_TORU);
            if (raw === null) return;
            const { value, savedAt } = JSON.parse(raw);
            if (typeof savedAt !== 'number' || Date.now() - savedAt > NAZWA_TORU_TTL_MS) {
                localStorage.removeItem(STORAGE_KEY_NAZWA_TORU);
                return;
            }
            elements.inputNazwaToru.value = value;
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
    }

    function saveNazwaToru() {
        try {
            localStorage.setItem(STORAGE_KEY_NAZWA_TORU, JSON.stringify({
                value: elements.inputNazwaToru.value,
                savedAt: Date.now()
            }));
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
    }

    // Persist "play ID tone after save" preference per browser (localStorage)
    const STORAGE_KEY_PLAY_ID_TONE = 'sgtimer_play_id_tone';

    function loadPlayIdTonePref() {
        try {
            elements.inputPlayIdTone.checked = localStorage.getItem(STORAGE_KEY_PLAY_ID_TONE) === '1';
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
    }

    function savePlayIdTonePref() {
        try {
            localStorage.setItem(STORAGE_KEY_PLAY_ID_TONE, elements.inputPlayIdTone.checked ? '1' : '0');
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
    }

    // Persist PAR limits (time limit / shot limit) per browser (localStorage)
    const STORAGE_KEY_PAR_SETUP = 'sgtimer_par_setup';

    function loadParSetup() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY_PAR_SETUP);
            if (!raw) return;
            const { time, shots } = JSON.parse(raw);
            if (typeof time === 'number' && time >= 0) elements.inputParTime.value = time;
            if (typeof shots === 'number' && shots >= 0) elements.inputParShots.value = shots;
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
    }

    function saveParSetup() {
        try {
            const { timeS, shotLimit } = getParLimits();
            localStorage.setItem(STORAGE_KEY_PAR_SETUP, JSON.stringify({ time: timeS, shots: shotLimit }));
        } catch (e) {
            console.warn('localStorage unavailable:', e);
        }
    }

    // Read and clamp the PAR limit inputs; time goes on the wire in 0.1s units
    // (2 bytes, capped below the 0xFFFF sentinel range)
    function getParLimits() {
        const timeTenths = Math.min(0xFFFE, Math.max(0, Math.round((parseFloat(elements.inputParTime.value) || 0) * 10)));
        const shotLimit = Math.min(0xFFFE, Math.max(0, parseInt(elements.inputParShots.value, 10) || 0));
        return { timeTenths, timeS: timeTenths / 10, shotLimit };
    }

    // Build the 6-byte PAR_SETUP frame [start_delay(2), time_limit(2), shot_limit(2)]
    // (big-endian) with the limits taken from the PAR card inputs
    function buildParBytes(delayTenths) {
        const { timeTenths, shotLimit } = getParLimits();
        return new Uint8Array([
            (delayTenths >> 8) & 0xFF, delayTenths & 0xFF,
            (timeTenths >> 8) & 0xFF, timeTenths & 0xFF,
            (shotLimit >> 8) & 0xFF, shotLimit & 0xFF
        ]);
    }

    function updateParStatus(timeS, shotLimit) {
        const timeStr = timeS > 0 ? `${timeS.toFixed(1)}s` : 'brak';
        const shotsStr = shotLimit > 0 ? shotLimit : 'brak';
        elements.parStatus.textContent = `W timerze: limit czasu ${timeStr} · limit strzalow ${shotsStr}`;
    }

    // Show the PAR limits currently stored on the device (read on connect)
    async function readParFromTimer() {
        if (!characteristics.parSetup) return;
        try {
            const value = await gattExec(() => characteristics.parSetup.readValue());
            if (value.byteLength < 6) return;
            const time = parseBigEndian(value, 2, 2) / 10;
            const shots = parseBigEndian(value, 4, 2);
            lastWrittenPar = { time, shots };
            updateParStatus(time, shots);
        } catch (e) {
            console.warn('PAR_SETUP read failed:', e);
        }
    }

    // Write the PAR limits to the device without starting a session.
    // PAR_SETUP is a single 6-byte frame, so start_delay is read back first
    // and preserved (falling back to 0 when the read fails).
    async function writeParToTimer() {
        if (!characteristics.parSetup) {
            alert('PAR_SETUP niedostepny na tym urzadzeniu.');
            return;
        }
        elements.btnWritePar.disabled = true;
        elements.btnResetPar.disabled = true;
        try {
            let delayHi = 0x00, delayLo = 0x00;
            try {
                const cur = await gattExec(() => characteristics.parSetup.readValue());
                if (cur.byteLength >= 2) {
                    delayHi = cur.getUint8(0);
                    delayLo = cur.getUint8(1);
                }
            } catch (e) {
                console.warn('PAR_SETUP read failed, keeping zero start delay:', e);
            }
            const { timeTenths, timeS, shotLimit } = getParLimits();
            const parData = new Uint8Array([
                delayHi, delayLo,
                (timeTenths >> 8) & 0xFF, timeTenths & 0xFF,
                (shotLimit >> 8) & 0xFF, shotLimit & 0xFF
            ]);
            await gattExec(() => characteristics.parSetup.writeValue(parData));
            lastWrittenPar = { time: timeS, shots: shotLimit };
            updateParStatus(timeS, shotLimit);
        } catch (error) {
            console.error('PAR_SETUP write failed:', error);
            alert('Blad zapisu PAR: ' + error.message);
        } finally {
            elements.btnWritePar.disabled = false;
            elements.btnResetPar.disabled = false;
        }
    }

    // Remove the PAR limits: zero both inputs and write 0/0 to the device,
    // so neither the timer nor the next start carries any limit
    async function resetParOnTimer() {
        elements.inputParTime.value = 0;
        elements.inputParShots.value = 0;
        saveParSetup();
        await writeParToTimer();
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
            `Zapisano: ${formatDate(cache.savedAt)} · sesji: ${cache.sessions.length}`;

        cache.sessions.forEach(session => {
            const { sessId, shots } = session;
            const li = document.createElement('li');
            li.className = 'session-item';
            const lastShot = shots[shots.length - 1];
            const meta = shots.length === 0
                ? 'Brak strzałów'
                : `${shots.length} strzałów · ${formatTime(lastShot.time)}s`;
            li.innerHTML = `
                <div>
                    <div class="session-date">${formatDate(sessId)}</div>
                    <div class="session-meta cache-label hidden" style="color: var(--accent); font-weight: 600;"></div>
                    <div class="session-meta">${meta}</div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button class="btn btn-outline btn-small cache-edit" style="margin-top: 0;" title="Przypisz tor / uczestnika">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                        </svg>
                    </button>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </div>
            `;
            // Device name comes from BLE — append via textContent, not innerHTML
            if (session.timerSn) {
                const metaEls = li.querySelectorAll('.session-meta');
                const metaEl = metaEls[metaEls.length - 1];
                metaEl.textContent += ` · ${session.timerSn}`;
            }
            // Labels are user-entered text — set via textContent, not innerHTML
            const label = [session.nazwaToru, session.uczestnik].filter(Boolean).join(' · ');
            if (label) {
                const labelEl = li.querySelector('.cache-label');
                labelEl.textContent = label;
                labelEl.classList.remove('hidden');
            }
            li.querySelector('.cache-edit').addEventListener('click', (ev) => {
                ev.stopPropagation();
                editCachedSessionLabels(sessId);
            });
            li.addEventListener('click', () => {
                // Supersede any in-flight BLE shot load so it does not
                // overwrite this instantly-rendered cached view
                shotsLoadToken++;
                document.querySelectorAll('.session-item').forEach(item => item.classList.remove('active'));
                li.classList.add('active');
                renderShots(sessId, shots, session);
            });
            elements.cacheList.appendChild(li);
        });
    }

    // Auto-save a finished live session into the cache — only when both
    // stage name and participant are filled in "Dane do kalkulatora"
    function saveLiveSessionToCache(sessId, shots, startDelay, parTime, parShots) {
        const nazwaToru = elements.inputNazwaToru.value.trim();
        const uczestnik = elements.inputUczestnik.value.trim();
        if (!nazwaToru || !uczestnik || !sessId || shots.length === 0) return false;

        const cache = readSessionCache() || { savedAt: sessId, sessions: [] };
        const entry = {
            sessId,
            shots: shots.map(s => ({ num: s.num, time: s.time })),
            nazwaToru,
            uczestnik
        };
        if (typeof startDelay === 'number') entry.startDelay = startDelay;
        if (parTime > 0) entry.parTime = parTime;
        if (parShots > 0) entry.parShots = parShots;
        if (deviceSerial) entry.timerSn = deviceSerial;
        const idx = cache.sessions.findIndex(s => s.sessId === sessId);
        if (idx >= 0) {
            cache.sessions[idx] = entry;
        } else {
            cache.sessions.push(entry);
            cache.sessions.sort((a, b) => b.sessId - a.sessId);
        }
        cache.savedAt = sessId;

        if (!writeSessionCache(cache)) return false;
        renderCacheCard();
        return true;
    }

    // Carry per-session labels over from the existing cache (incl. auto-saved
    // live sessions) so a re-download does not wipe them
    function applyCachedLabels(sessions) {
        const existing = readSessionCache();
        if (!existing) return;
        const byId = new Map(existing.sessions.map(s => [s.sessId, s]));
        sessions.forEach(s => {
            const old = byId.get(s.sessId);
            if (old) {
                if (old.nazwaToru) s.nazwaToru = old.nazwaToru;
                if (old.uczestnik) s.uczestnik = old.uczestnik;
                if (typeof old.startDelay === 'number') s.startDelay = old.startDelay;
                if (typeof old.parTime === 'number') s.parTime = old.parTime;
                if (typeof old.parShots === 'number') s.parShots = old.parShots;
                if (old.timerSn && !s.timerSn) s.timerSn = old.timerSn;
            }
        });
    }

    // Assign stage name and participant to a cached session (prompt-based,
    // prefilled from stored labels or the calculator form)
    function editCachedSessionLabels(sessId) {
        const cache = readSessionCache();
        const entry = cache ? cache.sessions.find(s => s.sessId === sessId) : null;
        if (!entry) return;

        const nazwaToru = prompt('Nazwa toru:', entry.nazwaToru || elements.inputNazwaToru.value);
        if (nazwaToru === null) return;
        const uczestnik = prompt('Uczestnik:', entry.uczestnik || elements.inputUczestnik.value);
        if (uczestnik === null) return;

        entry.nazwaToru = nazwaToru.trim();
        entry.uczestnik = uczestnik.trim();

        if (writeSessionCache(cache)) {
            // Keep an already-rendered shot view consistent with the new labels
            if (historySession.sessId === sessId) {
                historySession.nazwaToru = entry.nazwaToru;
                historySession.uczestnik = entry.uczestnik;
            }
            renderCacheCard();
        }
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
            if (characteristics.parSetup) {
                elements.parCard.classList.remove('hidden');
            }

            // Read device info (device name doubles as the timer serial number)
            deviceSerial = device.name || '';
            elements.deviceName.textContent = device.name || 'Nieznane';
            await readApiVersion();
            await readDeviceTime();
            await readParFromTimer();

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
        elements.parCard.classList.add('hidden');
        elements.parStatus.textContent = '';

        device = null;
        server = null;
        service = null;
        characteristics = {};
        currentSession = { id: null, active: false, shots: [], lastShotTime: 0 };
        lastWrittenPar = null;

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
            lastShotTime: 0,
            startDelay: startDelay,
            // PAR limits stored on the device when this session began
            parTime: lastWrittenPar ? lastWrittenPar.time : 0,
            parShots: lastWrittenPar ? lastWrittenPar.shots : 0
        };

        elements.sessionStatus.textContent = `Sesja rozpoczeta (opoznienie: ${startDelay}s)`;
        elements.currentTime.textContent = '0.00';
        elements.shotCount.textContent = 'Strzaly: 0';
        elements.btnStart.disabled = true;
        elements.btnStartPar.disabled = true;
        elements.btnStop.disabled = false;
        elements.btnSendToCalc.classList.add('hidden');
        elements.btnSaveToDb.classList.add('hidden');
        elements.btnSaveToDb.disabled = false;
        elements.dbSaveStatusLive.classList.add('hidden');
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
            elements.btnSaveToDb.classList.remove('hidden');
        }

        // Auto-cache the finished session when tor + uczestnik are filled in
        if (saveLiveSessionToCache(sessId, currentSession.shots, currentSession.startDelay,
                currentSession.parTime, currentSession.parShots)) {
            elements.sessionStatus.textContent += ' · zapisano w cache';
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
    // start_delay=0x0000 = immediate start; time/shot limits come from the PAR card
    async function startNow() {
        if (characteristics.parSetup) {
            try {
                const parData = buildParBytes(0);
                await gattExec(() => characteristics.parSetup.writeValue(parData));
                const { timeS, shotLimit } = getParLimits();
                lastWrittenPar = { time: timeS, shots: shotLimit };
                updateParStatus(timeS, shotLimit);
            } catch (error) {
                console.warn('PAR_SETUP write failed, starting without reset:', error);
            }
        }
        await sendCommand(CMD_SESSION_START);
    }

    // Start session with random delay 1.0–3.0s via PAR_SETUP (API 1.5)
    // Delay is randomized here in JS (device's built-in 0xFFFF would give 1–4s).
    // start_delay in 0.1s units (big-endian); time/shot limits come from the PAR card
    async function startWithRandomDelay() {
        if (!characteristics.parSetup) {
            alert('PAR_SETUP niedostepny — sprawdz konsole po poprawny UUID charakterystyki.');
            return;
        }
        try {
            // Random delay in tenths of a second within 1.0–3.0s (10..30 inclusive)
            const delayTenths = 10 + Math.floor(Math.random() * 21);
            const parData = buildParBytes(delayTenths);
            console.log('Writing PAR_SETUP to UUID:', BLE_CHAR_PAR_SETUP, 'delay(s):', delayTenths / 10, parData);
            await gattExec(() => characteristics.parSetup.writeValue(parData));
            const { timeS, shotLimit } = getParLimits();
            lastWrittenPar = { time: timeS, shots: shotLimit };
            updateParStatus(timeS, shotLimit);
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

                const entry = { sessId, shots };
                if (deviceSerial) entry.timerSn = deviceSerial;
                sessions.push(entry);
            }

            // Re-download must not wipe labels assigned to existing entries
            applyCachedLabels(sessions);

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
        elements.btnSaveHistoryToDb.classList.add('hidden');
        elements.btnSaveHistoryToDb.disabled = false;
        elements.dbSaveStatusHistory.classList.add('hidden');
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

    // Render the shot table for a session (shared by BLE load and cache view);
    // labels carries optional nazwaToru/uczestnik from a cached session
    function renderShots(sessId, shots, labels = {}) {
        elements.selectedSessionDate.textContent = formatDate(sessId);
        elements.shotsCard.classList.remove('hidden');
        elements.shotsLoading.classList.add('hidden');
        elements.shotsBody.innerHTML = '';

        if (shots.length === 0) {
            elements.shotsTable.classList.add('hidden');
            elements.noShots.classList.remove('hidden');
            elements.btnSendHistoryToCalc.classList.add('hidden');
            elements.btnSaveHistoryToDb.classList.add('hidden');
            return;
        }

        elements.noShots.classList.add('hidden');
        elements.shotsTable.classList.remove('hidden');

        // Store for calculator export (cached sessions carry their labels)
        historySession = { sessId, shots, nazwaToru: labels.nazwaToru, uczestnik: labels.uczestnik, startDelay: labels.startDelay, parTime: labels.parTime, parShots: labels.parShots, timerSn: labels.timerSn || deviceSerial };
        elements.btnSendHistoryToCalc.classList.remove('hidden');
        elements.btnSaveHistoryToDb.classList.remove('hidden');

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
        let opis = sessionDate + ' | ' + opisParts.join(' | ');
        if (typeof historySession.startDelay === 'number') {
            opis = sessionDate + ` | opoznienie startu ${historySession.startDelay}s | ` + opisParts.join(' | ');
        }

        const params = new URLSearchParams({
            liczba_strzalow: liczbaStrzalow,
            czas_bazowy: czasBazowy,
            opis: opis
        });
        appendCalcDataParams(params, historySession);
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
        let opis = opisParts.join(' | ');
        if (typeof currentSession.startDelay === 'number') {
            opis = `opoznienie startu ${currentSession.startDelay}s | ` + opis;
        }

        const params = new URLSearchParams({
            liczba_strzalow: liczbaStrzalow,
            czas_bazowy: czasBazowy,
            opis: opis
        });
        appendCalcDataParams(params);
        window.open(`https://piro-kalkulator.pifpaf.fun/?${params.toString()}`, '_blank');
    }

    // Append stage name and participant to calculator URL params (if filled in);
    // per-session labels (cached sessions) take precedence over the form inputs
    function appendCalcDataParams(params, overrides = {}) {
        const nazwaToru = (overrides.nazwaToru || elements.inputNazwaToru.value).trim();
        const uczestnik = (overrides.uczestnik || elements.inputUczestnik.value).trim();
        if (nazwaToru) params.set('nazwa_toru', nazwaToru);
        if (uczestnik) params.set('uczestnik', uczestnik);
    }

    const KALKULATOR_SAVE_URL = 'https://piro-kalkulator.pifpaf.fun/api_save.php';
    const KALKULATOR_EDIT_URL = 'https://piro-kalkulator.pifpaf.fun/edit.php';

    // ID tone signalling — lets Piro Overlay decode the saved session ID
    // straight from the camera's audio track (no manual typing on import).
    // Protocol v2 — MUST match piro_overlay.audio_sync.decode_id_tone (and
    // id_tone.js in the calculator) exactly: marker 5000 Hz ("code starts
    // here") + 4 digit tones + 1 checksum tone (position-weighted sum mod 10,
    // see idToneChecksum), one of 10 frequencies 5200-7000 Hz (step 200 Hz)
    // per digit 0-9, 300ms tone + 50ms gap each, whole sequence repeated
    // twice for redundancy. Band sits above the shot-timer buzzer band
    // (2000-4500 Hz) and below the Nyquist of Piro Overlay's 16kHz audio
    // extraction (8kHz). v2 rationale (measured on a real DJI recording of a
    // distant phone): tones >7kHz faded in the speaker->mic->AAC chain, so
    // the band top dropped from 7500 Hz; longer tones survive AAC eating the
    // quiet tail; the checksum lets the decoder reject a misread instead of
    // fetching someone else's session. NOT backward compatible with v1.
    const ID_TONE_MARKER_FREQ = 5000;
    const ID_TONE_DIGIT_FREQS = Array.from({ length: 10 }, (_, d) => 5200 + d * 200);
    const ID_TONE_TONE_DUR = 0.30;
    const ID_TONE_GAP = 0.05;
    const ID_TONE_REPEATS = 2;
    const ID_TONE_REPEAT_GAP = 0.3;
    const ID_TONE_MAX_ID = 9999;

    // MUST match piro_overlay.audio_sync._id_tone_checksum.
    function idToneChecksum(digits) {
        return digits.reduce((acc, d, i) => acc + (i + 1) * d, 0) % 10;
    }

    function playIdTone(sessionId) {
        const id = Number(sessionId);  // API może zwrócić id jako string
        if (!Number.isInteger(id) || id < 0 || id > ID_TONE_MAX_ID) {
            // Protocol only carries 4 digits — playing a truncated ID would
            // silently decode as a WRONG (but valid-looking) session on the
            // other end, which is worse than not signalling at all.
            console.warn(`playIdTone: ID ${sessionId} poza zasięgiem protokołu (0-${ID_TONE_MAX_ID}) — nie odtwarzam.`);
            return;
        }
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const digits = String(id).padStart(4, '0').split('').map(Number);
        digits.push(idToneChecksum(digits));

        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        osc.type = 'sine';
        const gain = ctx.createGain();
        gain.gain.value = 0.0001;
        osc.connect(gain).connect(ctx.destination);

        const ramp = 0.015;
        let t = ctx.currentTime + 0.05;
        const playTone = (freq, dur) => {
            const at = t;
            const offAt = at + dur;
            gain.gain.setValueAtTime(0.0001, at);
            gain.gain.exponentialRampToValueAtTime(1.0, at + ramp);
            osc.frequency.setValueAtTime(freq, at);
            gain.gain.setValueAtTime(1.0, offAt - ramp);
            gain.gain.exponentialRampToValueAtTime(0.0001, offAt);
            t = offAt;
        };

        osc.start();
        for (let r = 0; r < ID_TONE_REPEATS; r++) {
            playTone(ID_TONE_MARKER_FREQ, ID_TONE_TONE_DUR);
            t += ID_TONE_GAP;
            for (const d of digits) {
                playTone(ID_TONE_DIGIT_FREQS[d], ID_TONE_TONE_DUR);
                t += ID_TONE_GAP;
            }
            t += ID_TONE_REPEAT_GAP;
        }
        const stopAt = t;
        osc.stop(stopAt);
        osc.onended = () => ctx.close();
    }

    function addIdToneReplayButton(statusEl, sessionId) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline btn-small btn-id-tone-replay';
        btn.textContent = '🔊 Zagraj sygnał ID';
        btn.addEventListener('click', () => playIdTone(sessionId));
        statusEl.appendChild(btn);
    }

    // Link to the calculator's edit form for this entry — the per-entry
    // edit_token from api_save.php grants access to that one entry only
    // (the master edit key never leaves the calculator).
    function addDbEditLinkButton(statusEl, entryId, editToken) {
        const link = document.createElement('a');
        link.className = 'btn btn-outline btn-small btn-db-edit-link';
        link.textContent = '✏️ Edytuj wpis w bazie';
        link.href = `${KALKULATOR_EDIT_URL}?edit=${encodeURIComponent(entryId)}&token=${encodeURIComponent(editToken)}`;
        link.target = '_blank';
        link.rel = 'noopener';
        statusEl.appendChild(link);
    }

    function buildSavePayload(shots, overrides, includeDate) {
        if (!shots || shots.length === 0) return null;
        const lastShot = shots[shots.length - 1];
        const liczbaStrzalow = shots.length;
        const czasBazowy = parseFloat(formatTime(lastShot.time));

        let prevTime = 0;
        const opisParts = shots.map((shot, index) => {
            const split = index === 0 ? shot.time : shot.time - prevTime;
            prevTime = shot.time;
            const splitStr = index === 0 ? '' : ` (+${formatTime(split)}s)`;
            return `${shot.num}: ${formatTime(shot.time)}s${splitStr}`;
        });
        let opis = opisParts.join(' | ');

        if (includeDate) {
            const sessionDate = formatDate(overrides.sessId || 0);
            opis = typeof overrides.startDelay === 'number'
                ? `${sessionDate} | opoznienie startu ${overrides.startDelay}s | ${opis}`
                : `${sessionDate} | ${opis}`;
        } else if (typeof overrides.startDelay === 'number') {
            opis = `opoznienie startu ${overrides.startDelay}s | ${opis}`;
        }

        const nazwaToru = (overrides.nazwaToru || elements.inputNazwaToru.value).trim();
        const uczestnik = (overrides.uczestnik || elements.inputUczestnik.value).trim();

        const payload = { liczba_strzalow: liczbaStrzalow, czas_bazowy: czasBazowy, opis, nazwa_toru: nazwaToru, uczestnik };

        // Timer serial number and device session ID (unixtime-like) — sent only when available
        const timerSn = (overrides.timerSn || deviceSerial || '').trim();
        if (timerSn) payload.timer_sn = timerSn;
        if (overrides.sessId) payload.sess_id = overrides.sessId;

        // PAR limits active on the timer during this session — sent only when set,
        // stored in dedicated DB columns (opis stays untouched: the video overlay parses it)
        if (overrides.parTime > 0) payload.par_time_limit = overrides.parTime;
        if (overrides.parShots > 0) payload.par_shot_limit = overrides.parShots;

        return payload;
    }

    async function postToDatabase(payload, btn, statusEl) {
        btn.disabled = true;
        statusEl.classList.remove('hidden', 'error');
        statusEl.textContent = 'Zapisywanie...';
        try {
            const resp = await fetch(KALKULATOR_SAVE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (data.ok) {
                statusEl.textContent = `Zapisano! ID: #${data.id}`;
                addIdToneReplayButton(statusEl, data.id);
                if (data.edit_token) {
                    addDbEditLinkButton(statusEl, data.id, data.edit_token);
                }
                if (elements.inputPlayIdTone.checked) {
                    playIdTone(data.id);
                }
            } else {
                statusEl.classList.add('error');
                statusEl.textContent = `Błąd: ${data.error?.message || 'nieznany błąd'}`;
                btn.disabled = false;
            }
        } catch (e) {
            statusEl.classList.add('error');
            statusEl.textContent = 'Błąd połączenia';
            btn.disabled = false;
        }
    }

    // Save live session directly to database (no A/C/D scoring)
    function saveToDatabase() {
        const shots = currentSession.shots;
        const payload = buildSavePayload(shots, { sessId: currentSession.id, startDelay: currentSession.startDelay, parTime: currentSession.parTime, parShots: currentSession.parShots }, false);
        if (!payload) return;
        postToDatabase(payload, elements.btnSaveToDb, elements.dbSaveStatusLive);
    }

    // Save historical session directly to database (no A/C/D scoring)
    function saveHistoryToDatabase() {
        const { sessId, shots, nazwaToru, uczestnik, startDelay, parTime, parShots, timerSn } = historySession;
        const payload = buildSavePayload(shots, { sessId, nazwaToru, uczestnik, startDelay, parTime, parShots, timerSn }, true);
        if (!payload) return;
        postToDatabase(payload, elements.btnSaveHistoryToDb, elements.dbSaveStatusHistory);
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
    elements.btnSaveToDb.addEventListener('click', saveToDatabase);
    elements.btnSendHistoryToCalc.addEventListener('click', sendHistoryToCalculator);
    elements.btnSaveHistoryToDb.addEventListener('click', saveHistoryToDatabase);
    elements.inputNazwaToru.addEventListener('input', saveNazwaToru);
    elements.inputPlayIdTone.addEventListener('change', savePlayIdTonePref);
    elements.inputParTime.addEventListener('input', saveParSetup);
    elements.inputParShots.addEventListener('input', saveParSetup);
    elements.btnWritePar.addEventListener('click', writeParToTimer);
    elements.btnResetPar.addEventListener('click', resetParOnTimer);

    // Initialize
    checkBrowserSupport();
    loadNazwaToru();
    loadPlayIdTonePref();
    loadParSetup();
    renderCacheCard();
    </script>
</body>
</html>
