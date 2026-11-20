<?php

return [
    'enabled' => env('LIVE_PAX_ENABLED', true),
    'heartbeat_seconds' => (int) env('LIVE_PAX_HEARTBEAT_SECONDS', 15),
    'active_seconds' => (int) env('LIVE_PAX_ACTIVE_SECONDS', 25),
    'offline_seconds' => (int) env('LIVE_PAX_OFFLINE_SECONDS', 60),
    'retention_days' => (int) env('LIVE_PAX_RETENTION_DAYS', 30),
    'recording_enabled' => env('LIVE_PAX_RECORDING_ENABLED', true),
    'max_chunk_bytes' => (int) env('LIVE_PAX_MAX_CHUNK_BYTES', 800000),
    'sensitive_path_patterns' => [
        '#^/flight-payment(?:/|$)#i',
        '#^/flights-payment(?:/|$)#i',
        '#^/submit-flights-payment(?:/|$)#i',
        '#^/booking-successful(?:/|$)#i',
        '#^/authorization(?:/|$)#i',
    ],
];
