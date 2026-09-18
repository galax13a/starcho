# Trafikcams live console

The `/app/trafikcams` screen accepts either a WebSocket or Server-Sent Events
endpoint through `STARCHO_LIVE_ENDPOINT`.

```dotenv
STARCHO_LIVE_ENDPOINT=wss://live.example.com/trafikcams
STARCHO_LIVE_PROTOCOL=auto
```

Use `ws://`/`wss://` for WebSocket or `http://`/`https://` for SSE. The
`auto` protocol detects the transport from the URL.

Messages can be a single JSON object or `{ "events": [...] }`. The console
understands these optional fields:

```json
{
  "type": "vehicle_detected",
  "message": "Vehículo detectado",
  "camera": "CAM-01",
  "status": "online",
  "latency": 42,
  "timestamp": "2026-09-18T12:00:00Z"
}
```

The client marks the console `ONLINE` only after the transport opens or a
message arrives. It retries transport failures with exponential backoff,
shows the current transport and counters, and keeps authenticated HTML out of
the PWA cache.
