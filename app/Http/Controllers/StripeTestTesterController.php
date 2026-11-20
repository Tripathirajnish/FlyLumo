<?php

namespace App\Http\Controllers;

use App\Services\StripePaymentModeService as Mode;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Step F2b tester gate (owner-approved D2). Opening /stripe-test/tester/{token} with the
 * secret STRIPE_TESTER_TOKEN puts a 7-day cookie on THIS browser so it sees the Stripe
 * checkout while everyone else keeps the legacy flow. Wrong token -> plain 404.
 */
class StripeTestTesterController extends Controller
{
    public function on(string $token): Response
    {
        $mode = app(Mode::class);
        $value = $mode->testerCookieValue();

        if ($value === null || !$mode->isValidTesterToken($token)) {
            Log::warning('FARESHUNT_STRIPE_TESTER_TOKEN_REJECTED');
            abort(404);
        }

        Log::info('FARESHUNT_STRIPE_TESTER_ON');

        return $this->page('Stripe tester mode is ON for this browser (7 days).',
            'Only this browser will see the Stripe TEST checkout. Customers are not affected. To switch it off open /stripe-test/tester-off')
            ->cookie(Mode::TESTER_COOKIE, $value, Mode::TESTER_COOKIE_MINUTES, '/', null, true, true, false, 'lax');
    }

    public function off(): Response
    {
        return $this->page('Stripe tester mode is OFF for this browser.', 'This browser now uses the normal booking flow.')
            ->withoutCookie(Mode::TESTER_COOKIE, '/');
    }

    private function page(string $title, string $message): Response
    {
        $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow">'
            .'<meta name="viewport" content="width=device-width,initial-scale=1"><title>FaresHunt</title></head>'
            .'<body style="font-family:Arial,sans-serif;max-width:560px;margin:60px auto;padding:0 16px;color:#1f2937">'
            .'<h2>'.e($title).'</h2><p>'.e($message).'</p></body></html>';

        return response($html, 200)->header('Cache-Control', 'no-store')->header('X-Robots-Tag', 'noindex');
    }
}
