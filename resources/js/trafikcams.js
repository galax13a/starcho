/**
 * Trafikcams live console.
 *
 * The transport is intentionally browser-native: ws(s):// uses WebSocket and
 * http(s):// uses Server-Sent Events. The server only needs to emit JSON
 * messages; no vendor SDK is required in the panel.
 */
(() => {
    const root = document.getElementById('trafikcams-console');

    if (!root) return;

    const $ = (selector) => root.querySelector(selector);
    const endpoint = (root.dataset.liveEndpoint || '').trim();
    const configuredProtocol = (root.dataset.liveProtocol || 'auto').toLowerCase();
    const reconnectBase = Math.max(250, Number(root.dataset.reconnectBase || 1000));
    const reconnectMax = Math.max(reconnectBase, Number(root.dataset.reconnectMax || 30000));

    const elements = {
        statusLabel: $('#tc-status-label'),
        statusDetail: $('#tc-status-detail'),
        connect: $('#tc-connect'),
        connectLabel: $('#tc-connect-label'),
        sound: $('#tc-sound'),
        soundLabel: $('#tc-sound-label'),
        transport: $('#tc-transport-label'),
        latency: $('#tc-latency'),
        messages: $('#tc-messages'),
        reconnects: $('#tc-reconnects'),
        signal: $('#tc-signal-text'),
        install: $('#tc-install'),
        stream: $('#tc-event-stream'),
        streamEmpty: $('#tc-stream-empty'),
        cameras: $('#tc-camera-list'),
        cameraEmpty: $('#tc-camera-empty'),
        cameraCount: $('#tc-camera-count'),
    };

    const labels = {
        online: root.dataset.statusOnline,
        connecting: root.dataset.statusConnecting,
        disconnected: root.dataset.statusDisconnected,
        error: root.dataset.statusError,
        connect: root.dataset.connectLabel,
        disconnect: root.dataset.disconnectLabel,
        soundOn: root.dataset.soundOn,
        soundOff: root.dataset.soundOff,
        endpointMissing: root.dataset.endpointMissing,
        connectionLost: root.dataset.connectionLost,
        messageReceived: root.dataset.messageReceived,
        signalGood: root.dataset.signalGood,
        signalUnknown: root.dataset.signalUnknown,
        openingChannel: root.dataset.openingChannel,
        onlineWebsocket: root.dataset.onlineWebsocket,
        onlineSse: root.dataset.onlineSse,
        onlineMessage: root.dataset.onlineMessage,
        closedByOperator: root.dataset.closedByOperator,
        install: root.dataset.install,
    };

    const state = {
        status: 'disconnected',
        transport: 'unknown',
        socket: null,
        source: null,
        reconnectTimer: null,
        reconnectAttempts: 0,
        reconnectCount: 0,
        messageCount: 0,
        manualClose: false,
        sound: elements.sound?.checked ?? true,
        audioContext: null,
        cameras: new Map(),
    };
    let deferredInstallPrompt = null;

    function setStatus(status, detail) {
        state.status = status;
        root.dataset.status = status;

        if (elements.statusLabel) {
            elements.statusLabel.textContent = labels[status] || status;
        }

        if (elements.statusDetail && detail) {
            elements.statusDetail.textContent = detail;
        }

        if (elements.connect) {
            elements.connect.disabled = status === 'connecting';
            elements.connectLabel.textContent = status === 'online' ? labels.disconnect : labels.connect;
            elements.connect.setAttribute('aria-busy', status === 'connecting' ? 'true' : 'false');
        }

        if (elements.signal) {
            elements.signal.textContent = status === 'online' ? labels.signalGood : labels.signalUnknown;
        }
    }

    function setTransport(transport) {
        state.transport = transport;
        const labelsByTransport = {
            websocket: 'WebSocket',
            sse: 'SSE',
            unknown: 'Sin transporte',
        };

        if (elements.transport) elements.transport.textContent = labelsByTransport[transport] || labelsByTransport.unknown;
    }

    function protocolFor(url) {
        if (configuredProtocol === 'websocket' || configuredProtocol === 'ws') return 'websocket';
        if (configuredProtocol === 'sse' || configuredProtocol === 'eventsource') return 'sse';
        if (/^wss?:\/\//i.test(url)) return 'websocket';
        if (/^https?:\/\//i.test(url)) return 'sse';
        return 'unknown';
    }

    function clearReconnectTimer() {
        if (!state.reconnectTimer) return;
        window.clearTimeout(state.reconnectTimer);
        state.reconnectTimer = null;
    }

    function closeTransport() {
        if (state.socket) {
            state.socket.onopen = null;
            state.socket.onmessage = null;
            state.socket.onerror = null;
            state.socket.onclose = null;
            state.socket.close();
            state.socket = null;
        }

        if (state.source) {
            state.source.onopen = null;
            state.source.onmessage = null;
            state.source.onerror = null;
            state.source.close();
            state.source = null;
        }
    }

    function scheduleReconnect() {
        clearReconnectTimer();
        const delay = Math.min(reconnectMax, reconnectBase * (2 ** Math.min(state.reconnectAttempts, 5)));
        state.reconnectAttempts += 1;
        state.reconnectCount += 1;
        if (elements.reconnects) elements.reconnects.textContent = String(state.reconnectCount);
        state.reconnectTimer = window.setTimeout(() => connect(true), delay);
    }

    function fail(detail, retry = true) {
        closeTransport();
        setTransport('unknown');
        setStatus('error', detail);

        if (!state.manualClose && retry) scheduleReconnect();
        playTone('error');
    }

    function connect(isRetry = false) {
        if (state.status === 'connecting') return;

        if (!endpoint) {
            setStatus('error', labels.endpointMissing);
            setTransport('unknown');
            playTone('error');
            return;
        }

        clearReconnectTimer();
        closeTransport();
        state.manualClose = false;
        setStatus('connecting', isRetry ? labels.connectionLost : labels.openingChannel);

        const protocol = protocolFor(endpoint);

        if (protocol === 'websocket' && 'WebSocket' in window) {
            setTransport('websocket');
            state.socket = new WebSocket(endpoint);
            state.socket.onopen = () => {
                state.reconnectAttempts = 0;
                setStatus('online', labels.onlineWebsocket);
                playTone('connect');
            };
            state.socket.onmessage = (event) => handleMessage(event.data);
            state.socket.onerror = () => fail('El WebSocket devolvió un error.');
            state.socket.onclose = () => {
                if (!state.manualClose) fail(labels.connectionLost);
            };
            return;
        }

        if (protocol === 'sse' && 'EventSource' in window) {
            setTransport('sse');
            state.source = new EventSource(endpoint, { withCredentials: true });
            state.source.onopen = () => {
                state.reconnectAttempts = 0;
                setStatus('online', labels.onlineSse);
                playTone('connect');
            };
            state.source.onmessage = (event) => handleMessage(event.data);
            state.source.onerror = () => {
                if (!state.manualClose) fail(labels.connectionLost);
            };
            return;
        }

        fail(protocol === 'unknown' ? 'Endpoint inválido: usa ws(s):// o http(s)://.' : 'Este navegador no soporta el transporte configurado.', false);
    }

    function disconnect() {
        state.manualClose = true;
        clearReconnectTimer();
        closeTransport();
        setTransport('unknown');
        setStatus('disconnected', labels.closedByOperator);
    }

    function toggleConnection() {
        if (state.status === 'online' || state.status === 'connecting') {
            disconnect();
        } else {
            connect();
        }
    }

    function getAudioContext() {
        if (state.audioContext) return state.audioContext;
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return null;
        state.audioContext = new AudioContext();
        return state.audioContext;
    }

    function playTone(type) {
        if (!state.sound) return;
        const context = getAudioContext();
        if (!context) return;

        const settings = {
            connect: { frequency: 740, duration: .08, volume: .035 },
            message: { frequency: 520, duration: .045, volume: .018 },
            error: { frequency: 180, duration: .15, volume: .025 },
        }[type] || { frequency: 420, duration: .05, volume: .02 };

        if (context.state === 'suspended') context.resume().catch(() => {});
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        oscillator.type = type === 'error' ? 'sawtooth' : 'sine';
        oscillator.frequency.value = settings.frequency;
        gain.gain.setValueAtTime(settings.volume, context.currentTime);
        gain.gain.exponentialRampToValueAtTime(.001, context.currentTime + settings.duration);
        oscillator.connect(gain).connect(context.destination);
        oscillator.start();
        oscillator.stop(context.currentTime + settings.duration);
    }

    function textValue(value, fallback = '—') {
        if (value === null || value === undefined || value === '') return fallback;
        return String(value);
    }

    function formatTime(value = Date.now()) {
        const date = new Date(value);
        return Number.isNaN(date.getTime())
            ? new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
            : date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function normalizePayload(payload) {
        if (Array.isArray(payload)) return payload;
        if (Array.isArray(payload?.events)) return payload.events;
        return [payload];
    }

    function handleMessage(raw) {
        let payload;
        try {
            payload = typeof raw === 'string' ? JSON.parse(raw) : raw;
        } catch {
            payload = { message: raw, type: 'message' };
        }

        const events = normalizePayload(payload).filter(Boolean);
        events.forEach((item) => {
            state.messageCount += 1;
            renderEvent(item);
            updateCamera(item);
            updateLatency(item);
        });

        if (elements.messages) elements.messages.textContent = String(state.messageCount);
        if (state.status !== 'online') setStatus('online', labels.onlineMessage);
        playTone('message');
    }

    function renderEvent(item) {
        elements.streamEmpty?.remove();

        const event = document.createElement('article');
        const type = String(item.type || item.level || '').toLowerCase();
        event.className = `tc-event ${type.includes('error') ? 'tc-event--error' : (type.includes('warn') ? 'tc-event--warning' : '')}`;

        const dot = document.createElement('span');
        dot.className = 'tc-event-dot';
        dot.setAttribute('aria-hidden', 'true');

        const body = document.createElement('div');
        const title = document.createElement('div');
        title.className = 'tc-event-title';
        title.textContent = textValue(item.message || item.event || item.type, labels.messageReceived);
        const meta = document.createElement('div');
        meta.className = 'tc-event-meta';
        meta.textContent = [item.camera || item.camera_id || item.source, item.status].filter(Boolean).join(' · ') || 'Trafikcams';
        body.append(title, meta);

        const time = document.createElement('time');
        time.className = 'tc-event-time';
        const timestamp = new Date(item.timestamp || Date.now());
        time.dateTime = Number.isNaN(timestamp.getTime()) ? new Date().toISOString() : timestamp.toISOString();
        time.textContent = formatTime(item.timestamp || Date.now());
        event.append(dot, body, time);
        elements.stream?.prepend(event);

        while (elements.stream && elements.stream.children.length > 40) elements.stream.lastElementChild.remove();
    }

    function updateLatency(item) {
        const value = Number(item.latency ?? item.ping ?? NaN);
        if (Number.isFinite(value)) {
            elements.latency.textContent = String(Math.max(0, Math.round(value)));
            return;
        }

        if (item.timestamp) {
            const age = Date.now() - new Date(item.timestamp).getTime();
            if (Number.isFinite(age) && age >= 0 && age < 60000) elements.latency.textContent = String(Math.round(age));
        }
    }

    function updateCamera(item) {
        const camera = item.camera || item.camera_id || item.source;
        if (!camera) return;

        const key = String(camera);
        state.cameras.set(key, {
            name: key,
            status: String(item.status || item.camera_status || 'online').toLowerCase(),
            updatedAt: item.timestamp || Date.now(),
        });

        renderCameras();
    }

    function renderCameras() {
        elements.cameraEmpty?.remove();
        elements.cameras.querySelectorAll('.tc-camera').forEach((node) => node.remove());
        elements.cameraCount.textContent = String(state.cameras.size);

        [...state.cameras.values()].forEach((camera) => {
            const row = document.createElement('div');
            row.className = 'tc-camera';
            const icon = document.createElement('span');
            icon.className = 'tc-camera-icon';
            icon.textContent = '⌾';
            const copy = document.createElement('div');
            copy.className = 'tc-camera-copy';
            const name = document.createElement('div');
            name.className = 'tc-camera-name';
            name.textContent = camera.name;
            const detail = document.createElement('div');
            detail.className = 'tc-camera-detail';
            detail.textContent = `${camera.status} · ${formatTime(camera.updatedAt)}`;
            copy.append(name, detail);
            const stateDot = document.createElement('span');
            stateDot.className = `tc-camera-state ${camera.status.includes('error') ? 'is-error' : 'is-online'}`;
            stateDot.setAttribute('aria-label', camera.status);
            row.append(icon, copy, stateDot);
            elements.cameras.append(row);
        });
    }

    function toggleSound() {
        state.sound = elements.sound.checked;
        elements.soundLabel.textContent = state.sound ? labels.soundOn : labels.soundOff;
        if (state.sound) playTone('connect');
    }

    elements.connect?.addEventListener('click', toggleConnection);
    elements.sound?.addEventListener('change', toggleSound);

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        if (elements.install) elements.install.hidden = false;
    });

    elements.install?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) return;
        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        elements.install.hidden = true;
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        if (elements.install) elements.install.hidden = true;
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const isTyping = target instanceof HTMLElement && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));

        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
            event.preventDefault();
            toggleConnection();
        } else if (event.key === 'Escape' && !isTyping) {
            disconnect();
        } else if (event.code === 'Space' && !isTyping) {
            event.preventDefault();
            elements.sound.checked = !elements.sound.checked;
            toggleSound();
        }
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/trafikcams-sw.js', { scope: '/app/trafikcams' }).catch(() => {});
    }

    setTransport('unknown');
    setStatus('disconnected', elements.statusDetail?.textContent || '');
})();
