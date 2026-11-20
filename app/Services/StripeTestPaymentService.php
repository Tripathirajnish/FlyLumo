<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Stripe\StripeClient;

/**
 * Stripe client + mode gate. Step H / Step 1c (5 Nov 2026).
 * BOTH key sets live in .env (config stripe_checkout.test.* and .live.*).
 * The active mode is the CRM setting 'stripe_mode' (fallback: env STRIPE_MODE).
 * Live is honoured only when config('stripe_checkout.live_enabled') === true
 * AND an sk_live_ key is present; otherwise checkoutEnabled() is false and
 * client() throws, so customers fall back to the legacy flow (never a silent
 * test charge, never an unconfigured live charge).
 */
class StripeTestPaymentService
{
    /** Active mode for key selection: 'test' or 'live' as requested (not ceiling-gated). */
    public function mode(): string
    {
        return $this->requestedMode();
    }

    public function isLive(): bool
    {
        return $this->mode() === 'live';
    }

    public function liveAllowed(): bool
    {
        return config('stripe_checkout.live_enabled', false) === true;
    }

    /** CRM setting 'stripe_mode' first, then the env fallback. */
    protected function requestedMode(): string
    {
        try {
            $v = DB::table('settings')->where('name', 'stripe_mode')->orderByDesc('id')->value('value');
            if (is_string($v)) {
                $v = strtolower(trim($v));
                if ($v === 'live' || $v === 'test') {
                    return $v;
                }
            }
        } catch (\Throwable $e) {
            // settings table unavailable -> env fallback
        }

        return strtolower((string) config('stripe_checkout.mode', 'test')) === 'live' ? 'live' : 'test';
    }

    public function secret(): ?string
    {
        return config('stripe_checkout.'.$this->mode().'.secret');
    }

    public function publishableKey(): ?string
    {
        return config('stripe_checkout.'.$this->mode().'.key');
    }

    public function webhookSecret(): ?string
    {
        return config('stripe_checkout.'.$this->mode().'.webhook_secret');
    }

    public function client(): StripeClient
    {
        $live = $this->isLive();

        if ($live && !$this->liveAllowed()) {
            throw new RuntimeException('Stripe live mode is not enabled.');
        }

        $secret = $this->secret();
        $prefix = $live ? 'sk_live_' : 'sk_test_';

        if (!is_string($secret) || !str_starts_with($secret, $prefix)) {
            throw new RuntimeException('Stripe '.$this->mode().' secret key is required.');
        }

        return new StripeClient($secret);
    }

    public function amountToCents(string $amount): int
    {
        if (!preg_match('/^(?:0|[1-9][0-9]{0,6})\\.[0-9]{2}$/D', $amount)) {
            throw new RuntimeException('Invalid USD payment amount.');
        }

        [$dollars, $cents] = explode('.', $amount);
        $total = ((int) $dollars * 100) + (int) $cents;

        if ($total < 50 || $total > 99999999) {
            throw new RuntimeException('USD payment amount is outside permitted limits.');
        }

        return $total;
    }

    public function checkoutEnabled(): bool
    {
        if (config('stripe_checkout.enabled', false) !== true) {
            return false;
        }

        $live = $this->isLive();

        if ($live && !$this->liveAllowed()) {
            return false;
        }

        $secret = $this->secret();
        $prefix = $live ? 'sk_live_' : 'sk_test_';

        return is_string($secret) && str_starts_with($secret, $prefix);
    }
}
