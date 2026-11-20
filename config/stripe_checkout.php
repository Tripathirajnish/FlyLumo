<?php

return [
    'enabled' => env('STRIPE_TEST_CHECKOUT_ENABLED', false),
    'test_mode_only' => true,
    // Step H LIVE switch (5 Nov 2026). 'test' or 'live'. Live also needs
    // STRIPE_LIVE_ENABLED=true AND an sk_live_ secret key; nothing goes live by key alone.
    'mode' => env('STRIPE_MODE', 'test'),
    'live_enabled' => env('STRIPE_LIVE_ENABLED', false),
    // Step 1c (5 Nov 2026): BOTH key sets stay in .env; the active one is picked by mode.
    // Existing STRIPE_KEY/SECRET/WEBHOOK_SECRET act as the test set when STRIPE_TEST_* are absent.
    'test' => [
        'key' => env('STRIPE_TEST_KEY', env('STRIPE_KEY')),
        'secret' => env('STRIPE_TEST_SECRET', env('STRIPE_SECRET')),
        'webhook_secret' => env('STRIPE_TEST_WEBHOOK_SECRET', env('STRIPE_WEBHOOK_SECRET')),
    ],
    'live' => [
        'key' => env('STRIPE_LIVE_KEY'),
        'secret' => env('STRIPE_LIVE_SECRET'),
        'webhook_secret' => env('STRIPE_LIVE_WEBHOOK_SECRET'),
    ],
    'currency' => 'usd',
    'capture_method' => 'manual',
    // Server ceiling for FareLeaders (owner decision 4 Nov 2026). Even when
    // true, FareLeaders stays OFF until the flow is marked ready in code.
    'fareleaders_allowed' => env('STRIPE_FARELEADERS_ALLOWED', false),
    // Extra alert recipients (comma separated). The site contact email
    // (settings.content_emailid) always gets alerts too. Owner decision 4 Nov 2026.
    'alert_emails' => env('STRIPE_ALERT_EMAILS', ''),
    // Step F2b tester gate (owner-approved D2, 5 Nov 2026). While tester_only is true,
    // customers see Stripe ONLY in a browser that opened /stripe-test/tester/{token}.
    'tester_only' => env('STRIPE_TESTER_ONLY', true),
    'tester_token' => env('STRIPE_TESTER_TOKEN', ''),
    // Step F3b safety switch (5 Nov 2026): the booking executor calls the REAL supplier AeroBook
    // only when this is true. Default false: payment + fare check run, then manual review.
    // Owner turns it on for the F6 real TEST booking only.
    'executor_book_enabled' => env('STRIPE_EXECUTOR_BOOK', false),
];
