<x-layouts::app :title="__('trafikcams.title')">
    @php($liveEndpoint = config('starcho.live.endpoint'))

    <div
        id="trafikcams-console"
        class="tc-page"
        data-live-endpoint="{{ $liveEndpoint }}"
        data-live-protocol="{{ config('starcho.live.protocol', 'auto') }}"
        data-reconnect-base="{{ config('starcho.live.reconnect_base_ms', 1000) }}"
        data-reconnect-max="{{ config('starcho.live.reconnect_max_ms', 30000) }}"
        data-status-online="{{ __('trafikcams.online') }}"
        data-status-connecting="{{ __('trafikcams.connecting') }}"
        data-status-disconnected="{{ __('trafikcams.disconnected') }}"
        data-status-error="{{ __('trafikcams.error') }}"
        data-connect-label="{{ __('trafikcams.connect') }}"
        data-disconnect-label="{{ __('trafikcams.disconnect') }}"
        data-sound-on="{{ __('trafikcams.sound_on') }}"
        data-sound-off="{{ __('trafikcams.sound_off') }}"
        data-endpoint-missing="{{ __('trafikcams.endpoint_missing') }}"
        data-connection-lost="{{ __('trafikcams.connection_lost') }}"
        data-message-received="{{ __('trafikcams.message_received') }}"
        data-signal-good="{{ __('trafikcams.signal_good') }}"
        data-signal-unknown="{{ __('trafikcams.signal_unknown') }}"
        data-opening-channel="{{ __('trafikcams.opening_channel') }}"
        data-online-websocket="{{ __('trafikcams.online_websocket') }}"
        data-online-sse="{{ __('trafikcams.online_sse') }}"
        data-online-message="{{ __('trafikcams.online_message') }}"
        data-closed-by-operator="{{ __('trafikcams.closed_by_operator') }}"
        data-install="{{ __('trafikcams.install') }}"
    >
        <header class="tc-topline">
            <div class="tc-brandline">
                <span class="tc-brand-mark">//</span>
                <span class="tc-brand-name">{{ __('trafikcams.eyebrow') }}</span>
                <span class="tc-brand-id">001</span>
            </div>
            <div class="tc-transport" id="tc-transport" aria-live="polite">
                <span class="tc-transport-dot"></span>
                <span id="tc-transport-label">{{ __('trafikcams.transport_unknown') }}</span>
                <button id="tc-install" class="tc-install" type="button" hidden>{{ __('trafikcams.install') }}</button>
            </div>
        </header>

        <section class="tc-hero" aria-labelledby="tc-title">
            <div class="tc-hero-copy">
                <p class="tc-eyebrow">{{ __('trafikcams.eyebrow') }} <span>/// LIVE CONSOLE</span></p>
                <h1 id="tc-title" class="tc-title"><span>TRAFIK</span><strong>CAMS</strong><em>V1</em></h1>
                <p class="tc-subtitle"><span>CONVIERTE</span> <b>SEÑAL</b> <small>EN IMPULSO</small></p>
            </div>

            <div class="tc-status-card" aria-label="{{ __('trafikcams.status') }}">
                <div class="tc-status-heading">
                    <span class="tc-status-dot" id="tc-status-dot"></span>
                    <span class="tc-status-label" id="tc-status-label">{{ __('trafikcams.disconnected') }}</span>
                    <span class="tc-status-pulse" id="tc-status-pulse" aria-hidden="true"></span>
                </div>
                <div class="tc-status-detail" id="tc-status-detail">{{ __('trafikcams.waiting_hint') }}</div>
                <div class="tc-action-row">
                    <button id="tc-connect" class="tc-button tc-button-primary" type="button">
                        <span class="tc-button-icon" aria-hidden="true">●</span>
                        <span id="tc-connect-label">{{ __('trafikcams.connect') }}</span>
                        <kbd>Ctrl ↵</kbd>
                    </button>
                    <label class="tc-sound-toggle" title="{{ __('trafikcams.sound') }}">
                        <input id="tc-sound" type="checkbox" checked>
                        <span class="tc-toggle-track"><span class="tc-toggle-thumb"></span></span>
                        <span class="tc-sound-label" id="tc-sound-label">{{ __('trafikcams.sound_on') }}</span>
                    </label>
                </div>
            </div>
        </section>

        <section class="tc-metrics" aria-label="{{ __('trafikcams.status') }}">
            <div class="tc-metric"><span>{{ __('trafikcams.latency') }}</span><strong id="tc-latency">—</strong><small>ms</small></div>
            <div class="tc-metric"><span>{{ __('trafikcams.messages') }}</span><strong id="tc-messages">0</strong><small>evt</small></div>
            <div class="tc-metric"><span>{{ __('trafikcams.reconnects') }}</span><strong id="tc-reconnects">0</strong><small>x</small></div>
            <div class="tc-metric tc-metric-signal"><span>{{ __('trafikcams.status') }}</span><strong id="tc-signal-text">{{ __('trafikcams.signal_unknown') }}</strong><i id="tc-signal-bars" class="tc-signal-bars" aria-hidden="true"><b></b><b></b><b></b><b></b></i></div>
        </section>

        <div class="tc-main-grid">
            <section class="tc-panel tc-stream-panel" aria-labelledby="tc-stream-title">
                <div class="tc-panel-header">
                    <div><span class="tc-panel-kicker">/// REAL TIME</span><h2 id="tc-stream-title">{{ __('trafikcams.event_stream') }}</h2></div>
                    <span class="tc-live-badge"><i></i> LIVE</span>
                </div>
                <div id="tc-event-stream" class="tc-event-stream" aria-live="polite">
                    <div id="tc-stream-empty" class="tc-empty-state">
                        <span class="tc-empty-glyph">⌁</span>
                        <strong>{{ __('trafikcams.waiting') }}</strong>
                        <span>{{ __('trafikcams.waiting_hint') }}</span>
                    </div>
                </div>
            </section>

            <aside class="tc-panel tc-camera-panel" aria-labelledby="tc-cameras-title">
                <div class="tc-panel-header">
                    <div><span class="tc-panel-kicker">/// MONITOR</span><h2 id="tc-cameras-title">{{ __('trafikcams.cameras') }}</h2></div>
                    <span id="tc-camera-count" class="tc-count">0</span>
                </div>
                <div id="tc-camera-list" class="tc-camera-list">
                    <div id="tc-camera-empty" class="tc-empty-state tc-empty-state-small">
                        <span class="tc-empty-glyph">◌</span>
                        <span>{{ __('trafikcams.no_cameras') }}</span>
                    </div>
                </div>
            </aside>
        </div>

        <footer class="tc-footer">
            <span>{{ __('trafikcams.shortcuts') }}</span>
            <span><kbd>Ctrl</kbd><kbd>↵</kbd> {{ __('trafikcams.shortcut_connect') }}</span>
            <span><kbd>Esc</kbd> {{ __('trafikcams.shortcut_disconnect') }}</span>
            <span><kbd>Space</kbd> {{ __('trafikcams.shortcut_sound') }}</span>
            <span class="tc-footer-build">TRAFIKCAMS / 001</span>
        </footer>
    </div>
</x-layouts::app>
