<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Single decision point: "Stripe or legacy flow?" (owner-approved 4 Nov 2026).
 *
 * Stripe is used for a flow ONLY when ALL are true:
 *   1. Server .env ceiling allows it (STRIPE_TEST_CHECKOUT_ENABLED, test key,
 *      test_mode_only; FareLeaders also needs STRIPE_FARELEADERS_ALLOWED).
 *   2. CRM switch for that flow is exactly 'ON' (settings table; missing = OFF).
 *   3. The flow is marked ready in code.
 * Anything else - including any exception - means Stripe is OFF and the
 * existing legacy booking / card flow is used unchanged.
 *
 * Secrets are never returned or logged.
 */
class StripePaymentModeService
{
    public const FLOW_WEBSITE = 'website';
    public const FLOW_FARELEADERS = 'fareleaders';

    public const SETTING_KEYS = [
        self::FLOW_WEBSITE => 'stripe_website_enabled',
        self::FLOW_FARELEADERS => 'stripe_fareleaders_enabled',
    ];

    public function isStripeEnabledFor(string $flow): bool
    {
        try {
            return $this->serverAllows($flow)
                && $this->isFlowReady($flow)
                && $this->crmSwitch($flow) === 'ON';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Step F2b: decision for CUSTOMER-facing entry points (checkout start, return pages,
     * booking page). Same as isStripeEnabledFor() plus the tester gate: while
     * stripe_checkout.tester_only is true, only a browser holding the tester cookie
     * gets Stripe; everyone else keeps the legacy flow. Webhook / booking executor
     * do not use this (they act on attempts that already exist).
     */
    public function isStripeEnabledForCustomer(string $flow): bool
    {
        try {
            if (!$this->isStripeEnabledFor($flow)) {
                return false;
            }
            if (config('stripe_checkout.tester_only', true) === false) {
                return true;
            }

            return $this->isTesterRequest();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public const TESTER_COOKIE = 'fh_stripe_tester';
    public const TESTER_COOKIE_MINUTES = 10080; // 7 days

    /** Cookie value for a valid tester; null when no usable token is configured. */
    public function testerCookieValue(): ?string
    {
        $token = (string) config('stripe_checkout.tester_token', '');
        if (strlen($token) < 32) {
            return null;
        }

        return hash_hmac('sha256', 'fareshunt-stripe-tester-v1', $token);
    }

    public function isValidTesterToken(string $given): bool
    {
        $token = (string) config('stripe_checkout.tester_token', '');

        return strlen($token) >= 32 && hash_equals($token, $given);
    }

    protected function isTesterRequest(): bool
    {
        $expected = $this->testerCookieValue();
        if ($expected === null || !app()->bound('request')) {
            return false;
        }
        $cookie = request()->cookie(self::TESTER_COOKIE);

        return is_string($cookie) && hash_equals($expected, $cookie);
    }

    /**
     * Read-only snapshot for the CRM panel (Step G). No secrets.
     */
    public function status(): array
    {
        $flows = [];

        foreach (array_keys(self::SETTING_KEYS) as $flow) {
            try {
                $switch = $this->crmSwitch($flow);
            } catch (\Throwable $e) {
                $switch = 'ERROR';
            }

            $flows[$flow] = [
                'server_allows' => $this->safeBool(fn () => $this->serverAllows($flow)),
                'ready' => $this->safeBool(fn () => $this->isFlowReady($flow)),
                'crm_switch' => $switch === 'ON' ? 'ON' : ($switch === 'ERROR' ? 'ERROR' : 'OFF'),
                'effective' => $this->isStripeEnabledFor($flow) ? 'STRIPE' : 'LEGACY',
            ];
        }

        return [
            'mode' => $this->keyMode(),
            'flows' => $flows,
            'tester_only' => config('stripe_checkout.tester_only', true) !== false,
            'tester_token_set' => $this->testerCookieValue() !== null,
        ];
    }

    protected function serverAllows(string $flow): bool
    {
        if (!array_key_exists($flow, self::SETTING_KEYS)) {
            return false;
        }

        if (!app(StripeTestPaymentService::class)->checkoutEnabled()) {
            return false;
        }

        if ($flow === self::FLOW_FARELEADERS) {
            return config('stripe_checkout.fareleaders_allowed') === true;
        }

        return true;
    }

    /**
     * Both flows are built (FareLeaders: owner-approved FL-A/FL-B, 5 Nov 2026).
     * FareLeaders still needs .env STRIPE_FARELEADERS_ALLOWED=true AND its CRM
     * switch ON; otherwise the legacy FareLeaders flow is used unchanged.
     */
    protected function isFlowReady(string $flow): bool
    {
        return in_array($flow, [self::FLOW_WEBSITE, self::FLOW_FARELEADERS], true);
    }

    /**
     * Returns the stored switch value, or 'OFF' when no row exists.
     * The settings table has no unique index on name: the newest row wins.
     */
    protected function crmSwitch(string $flow): string
    {
        $name = self::SETTING_KEYS[$flow] ?? null;

        if ($name === null) {
            return 'OFF';
        }

        $value = DB::table('settings')
            ->where('name', $name)
            ->orderByDesc('id')
            ->value('value');

        return is_string($value) ? trim($value) : 'OFF';
    }

    protected function keyMode(): string
    {
        $secret = app(StripeTestPaymentService::class)->secret();

        if (!is_string($secret) || $secret === '') {
            return 'NONE';
        }

        if (str_starts_with($secret, 'sk_test_')) {
            return 'TEST';
        }

        if (str_starts_with($secret, 'sk_live_')) {
            return 'LIVE';
        }

        return 'UNKNOWN';
    }

    private function safeBool(callable $fn): bool
    {
        try {
            return (bool) $fn();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
