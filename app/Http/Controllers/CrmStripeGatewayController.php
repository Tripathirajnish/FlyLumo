<?php

namespace App\Http\Controllers;

use App\Services\StripePaymentModeService as Mode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CRM -> Payment Gateway (Stripe) panel. Step G, owner-approved 4 Nov 2026.
 *
 * - Super Admin ONLY (role slug check here, on top of CrmPermissionGate).
 * - Per-flow ON/OFF switch stored in `settings` (stripe_website_enabled /
 *   stripe_fareleaders_enabled). Default / missing = OFF = legacy flow.
 * - The switch can never override the server .env ceiling or "not ready".
 * - Every change is written to crm_change_logs (module stripe_gateway).
 * - Never shows keys or secrets; only TEST/LIVE mode and yes/no flags.
 * - Step G3: read-only "Needs attention" list + webhook health (StripeAttentionService).
 * - Step G5: capture / cancel hold / refund (full amount) via StripeAttemptActionService.
 * - Step 1c-UI (5 Nov 2026): Test/Live mode switch stored in settings.stripe_mode.
 *   Both key sets stay in .env; the CRM only picks which one is active. Live is
 *   possible only when .env STRIPE_LIVE_ENABLED=true and all live keys are set.
 */
class CrmStripeGatewayController extends Controller
{
    public function index(Request $request)
    {
        if ($r = $this->guard($request)) return $r;

        $mode = app(Mode::class);
        $status = $mode->status();
        $secret = app(\App\Services\StripeTestPaymentService::class)->webhookSecret();
        $webhookReady = is_string($secret) && str_starts_with($secret, 'whsec_');
        $modeInfo = $this->modeInfo();

        $attemptCounts = Schema::hasTable('stripe_payment_attempts')
            ? DB::table('stripe_payment_attempts')->select('status', DB::raw('COUNT(*) c'))->groupBy('status')->pluck('c', 'status')->all()
            : [];

        $recentAttempts = Schema::hasTable('stripe_payment_attempts')
            ? DB::table('stripe_payment_attempts')->orderByDesc('id')->limit(20)
                ->get(['id', 'flow', 'amount_cents', 'currency', 'status', 'booking_status', 'last_error_code', 'created_at', 'authorized_at', 'captured_at', 'cancelled_at'])
            : collect();

        $audit = Schema::hasTable('crm_change_logs')
            ? DB::table('crm_change_logs as l')->leftJoin('admins as a', 'a.id', '=', 'l.admin_id')
                ->where('l.module', 'stripe_gateway')->orderByDesc('l.id')->limit(20)
                ->get(['l.record_key', 'l.old_value', 'l.new_value', 'l.created_at', 'a.name as admin_name'])
            : collect();

        $attention = app(\App\Services\StripeAttentionService::class);
        $needsAttention = $attention->attention();
        $webhookHealth = $attention->webhookHealth();

        return view('crm.stripe-gateway', compact('status', 'webhookReady', 'attemptCounts', 'recentAttempts', 'audit', 'needsAttention', 'webhookHealth', 'modeInfo'));
    }

    public function update(Request $request)
    {
        if ($r = $this->guard($request)) return $r;

        // Step G5: capture / cancel hold / refund one Stripe payment (same Super-Admin guard + route).
        if ($request->filled('attempt_action')) {
            return $this->attemptAction($request);
        }

        // Step 1c-UI: switch Stripe Test/Live mode (same Super-Admin guard + route).
        if ($request->filled('stripe_mode')) {
            return $this->modeSwitch($request);
        }

        $data = $request->validate([
            'flow' => ['required', 'in:website,fareleaders'],
            'state' => ['required', 'in:ON,OFF'],
            'confirm' => ['nullable', 'in:1'],
        ]);

        $flow = $data['flow'];
        $state = $data['state'];
        $mode = app(Mode::class);
        $flowStatus = $mode->status()['flows'][$flow] ?? null;

        if ($state === 'ON') {
            if (($data['confirm'] ?? null) !== '1') {
                return back()->withErrors(['confirm' => 'Please tick the confirmation box to turn Stripe ON.']);
            }
            if (!$flowStatus || $flowStatus['ready'] !== true) {
                return back()->withErrors(['flow' => 'This booking flow is not ready for Stripe yet. It stays on the existing flow.']);
            }
        }

        $name = Mode::SETTING_KEYS[$flow];
        $adminId = (int) $request->session()->get('crm_admin_id');

        DB::transaction(function () use ($name, $state, $adminId) {
            $row = DB::table('settings')->where('name', $name)->orderByDesc('id')->lockForUpdate()->first();
            $old = $row ? trim((string) $row->value) : 'OFF (not set)';

            if ($row) {
                DB::table('settings')->where('id', $row->id)->update(['value' => $state, 'updated_at' => now()]);
            } else {
                DB::table('settings')->insert(['name' => $name, 'value' => $state, 'created_at' => now(), 'updated_at' => now()]);
            }

            if (Schema::hasTable('crm_change_logs')) {
                DB::table('crm_change_logs')->insert([
                    'module' => 'stripe_gateway',
                    'record_key' => $name,
                    'field' => 'value',
                    'action' => 'update',
                    'old_value' => $old,
                    'new_value' => $state,
                    'admin_id' => $adminId ?: null,
                    'created_at' => now(),
                ]);
            }
        });

        $effective = $mode->isStripeEnabledFor($flow) ? 'Stripe' : 'existing (legacy) flow';
        $label = $flow === 'website' ? 'Website bookings' : 'FareLeaders bookings';

        return redirect()->route('crm.stripe_gateway')->with('crm_success',
            "Stripe switch for {$label} set to {$state}. Customers now use: {$effective}.");
    }

    private function attemptAction(Request $request)
    {
        $data = $request->validate([
            'attempt_id' => ['required', 'integer', 'min:1'],
            'attempt_action' => ['required', 'in:capture,cancel,refund'],
            'reason' => ['required', 'string', 'min:5', 'max:300'],
            'confirm' => ['required', 'in:1'],
        ], [
            'confirm.required' => 'Please tick the confirmation box.',
            'reason.required' => 'Please write a short reason (at least 5 characters).',
        ]);

        $adminId = (int) $request->session()->get('crm_admin_id');

        try {
            $msg = app(\App\Services\StripeAttemptActionService::class)
                ->run((int) $data['attempt_id'], $data['attempt_action'], $adminId, trim($data['reason']));
        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Illuminate\Support\Facades\Log::warning('FARESHUNT_STRIPE_CRM_ACTION_FAILED', ['attempt_id' => (int) $data['attempt_id'], 'action' => $data['attempt_action'], 'code' => $e->getStripeCode()]);

            return back()->withErrors(['attempt_action' => 'Stripe refused this action: '.mb_substr($e->getMessage(), 0, 200)]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['attempt_action' => $e->getMessage()]);
        }

        return back()->with('crm_success', $msg);
    }

    /** Step 1c-UI: switch the active Stripe mode. Never touches .env or keys. */
    private function modeSwitch(Request $request)
    {
        $data = $request->validate([
            'stripe_mode' => ['required', 'in:test,live'],
            'reason' => ['required', 'string', 'min:5', 'max:300'],
            'confirm' => ['required', 'in:1'],
            'force_inflight' => ['nullable', 'in:1'],
        ], [
            'confirm.required' => 'Please tick the confirmation box.',
            'reason.required' => 'Please write a short reason (at least 5 characters).',
        ]);

        $target = $data['stripe_mode'];
        $pay = app(\App\Services\StripeTestPaymentService::class);
        $current = $pay->mode();

        if ($target === $current) {
            return back()->withErrors(['stripe_mode' => 'Stripe is already in '.strtoupper($target).' mode. Nothing changed.']);
        }
        if ($target === 'live' && !$pay->liveAllowed()) {
            return back()->withErrors(['stripe_mode' => 'Live mode is not allowed by the server (.env STRIPE_LIVE_ENABLED is off). Nothing changed.']);
        }
        if (!$this->keysReady($target)) {
            return back()->withErrors(['stripe_mode' => strtoupper($target).' keys are not fully set on the server (secret, publishable and webhook secret). Nothing changed.']);
        }

        $inflight = $this->inflightIds();
        if ($inflight && ($data['force_inflight'] ?? null) !== '1') {
            return back()->withErrors(['stripe_mode' => 'Payments still in progress in '.strtoupper($current).' mode: #'.implode(', #', $inflight)
                .'. Finish or cancel them first, or tick "switch anyway" (they can only be managed again after switching back).']);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');
        $reason = trim($data['reason']);

        DB::transaction(function () use ($target, $current, $adminId, $reason, $inflight) {
            $row = DB::table('settings')->where('name', 'stripe_mode')->orderByDesc('id')->lockForUpdate()->first();

            if ($row) {
                DB::table('settings')->where('id', $row->id)->update(['value' => $target, 'updated_at' => now()]);
            } else {
                DB::table('settings')->insert(['name' => 'stripe_mode', 'value' => $target, 'created_at' => now(), 'updated_at' => now()]);
            }

            if (Schema::hasTable('crm_change_logs')) {
                $note = ' | reason: '.$reason.($inflight ? ' | switched with in-progress #'.implode(',#', $inflight) : '');
                DB::table('crm_change_logs')->insert([
                    'module' => 'stripe_gateway',
                    'record_key' => 'stripe_mode',
                    'field' => 'value',
                    'action' => 'update',
                    'old_value' => strtoupper($current).($row ? '' : ' (server default)'),
                    'new_value' => mb_substr(strtoupper($target).$note, 0, 500),
                    'admin_id' => $adminId ?: null,
                    'created_at' => now(),
                ]);
            }
        });

        $now = $pay->mode();
        $ok = $pay->checkoutEnabled() ? 'Stripe checkout is working in this mode.' : 'WARNING: Stripe checkout is NOT available in this mode, customers use the existing flow.';

        return redirect()->route('crm.stripe_gateway')->with('crm_success',
            'Stripe mode switched from '.strtoupper($current).' to '.strtoupper($now).'. '.$ok);
    }

    /** Step 1c-UI: what the mode panel shows. Only yes/no flags, never key values. */
    private function modeInfo(): array
    {
        $pay = app(\App\Services\StripeTestPaymentService::class);

        try {
            $row = DB::table('settings')->where('name', 'stripe_mode')->orderByDesc('id')->value('value');
        } catch (\Throwable $e) {
            $row = null;
        }
        $row = is_string($row) ? strtolower(trim($row)) : '';

        return [
            'active' => $pay->mode(),
            'source' => in_array($row, ['test', 'live'], true) ? 'CRM switch' : 'server (.env) default',
            'live_allowed' => $pay->liveAllowed(),
            'live_ready' => $this->keysReady('live'),
            'test_ready' => $this->keysReady('test'),
            'checkout_enabled' => $pay->checkoutEnabled(),
            'inflight' => $this->inflightIds(),
        ];
    }

    /** All three keys of one mode are present with the right prefixes. */
    private function keysReady(string $mode): bool
    {
        $m = $mode === 'live' ? 'live' : 'test';
        $s = config("stripe_checkout.{$m}.secret");
        $k = config("stripe_checkout.{$m}.key");
        $w = config("stripe_checkout.{$m}.webhook_secret");

        return is_string($s) && str_starts_with($s, "sk_{$m}_")
            && is_string($k) && str_starts_with($k, "pk_{$m}_")
            && is_string($w) && str_starts_with($w, 'whsec_');
    }

    /** Payments still in progress: money on hold (any age) or checkout opened in the last 24 hours. */
    private function inflightIds(): array
    {
        if (!Schema::hasTable('stripe_payment_attempts')) {
            return [];
        }

        return DB::table('stripe_payment_attempts')
            ->where(fn ($q) => $q->where('status', 'authorized')
                ->orWhere(fn ($w) => $w->whereIn('status', ['created', 'pending'])->where('created_at', '>=', now()->subHours(24))))
            ->orderBy('id')->limit(50)->pluck('id')->map(fn ($i) => (int) $i)->all();
    }

    /** Logged-in, active, Super Admin only. Everyone else: 403. */
    private function guard(Request $request)
    {
        $adminId = (int) $request->session()->get('crm_admin_id');

        if (!$adminId) {
            return redirect()->route('crm.login');
        }

        $slug = Schema::hasTable('crm_access_roles')
            ? (string) DB::table('admins as a')
                ->leftJoin('crm_access_roles as r', 'a.crm_access_role_id', '=', 'r.id')
                ->where('a.id', $adminId)->where('a.status', '1')->where('r.is_active', 1)
                ->when(Schema::hasColumn('admins', 'crm_deleted_at'), fn ($q) => $q->whereNull('a.crm_deleted_at'))
                ->value('r.slug')
            : '';

        abort_unless($slug === 'super-admin', 403, 'Only a Super Admin can view or change the payment gateway.');

        return null;
    }
}
