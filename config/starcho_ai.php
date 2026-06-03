<?php

/*
|--------------------------------------------------------------------------
| Starcho AI runtime settings
|--------------------------------------------------------------------------
| Controls how long the app waits (synchronously) for an AI response and
| when a request should be pushed to a background job instead of blocking.
*/

return [

    // Seconds PHP will wait for a synchronous AI response before giving up.
    // Used for set_time_limit() and as the HTTP client timeout. Default 120s.
    'request_timeout' => (int) env('STARCHO_AI_TIMEOUT', 120),

    // If a generation is expected to take longer than this many seconds, it is
    // handled as a background job and the user is notified when it's ready.
    'async_threshold' => (int) env('STARCHO_AI_ASYNC_THRESHOLD', 60),

    // Extra head-room added to set_time_limit() over request_timeout.
    'time_limit_buffer' => (int) env('STARCHO_AI_TIME_LIMIT_BUFFER', 15),
];
