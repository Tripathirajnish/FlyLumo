<?php

namespace App\Http\Controllers;

use App\Services\StripeReturnStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Stripe-only customer return pages (Step D, live status Step F5).
 *
 * SECURITY: these pages are reached by a browser redirect, which anyone can
 * fake. They therefore NEVER change payment or booking state. Only the
 * signature-verified Stripe webhook (Step E) may mark an attempt authorized.
 * F5: the pages only need a known 64-hex attempt reference (not the tester switch),
 * so a customer who already paid never sees a 404 if Stripe is switched off later.
 */
class StripeTestReturnController extends Controller
{
    public function success(string $reference): \Symfony\Component\HttpFoundation\Response
    {
        $s = $this->statusOr404($reference);

        // F5b: booking already confirmed -> straight to the booking page.
        if ($s['state'] === 'confirmed' && !empty($s['booking_url'])) {
            return redirect()->away($s['booking_url'])->header('Cache-Control', 'no-store');
        }

        return $this->page($s['title'], $s['message'], $reference, $s);
    }

    public const CANCEL_BACK_MESSAGE = 'Payment not completed. No money has been taken from your card. Your details are saved - please check them and press Pay to try again.';
    public const CANCEL_BACK_MAX_AGE = 21600;   // 6 h

    public function cancel(string $reference): \Symfony\Component\HttpFoundation\Response
    {
        $this->statusOr404($reference);

        // 19 Nov 2026: back to the customer's own booking page (same browser session, details
        // pre-filled) instead of a dead-end page whose only link is the homepage. Only a URL on
        // our own site that booking-start put in this session is used; otherwise the old page.
        $back = session('FH-Stripe-Back');
        $site = rtrim(url('/'), '/').'/';
        if (is_array($back) && is_string($back['url'] ?? null) && str_starts_with($back['url'], $site)
            && strlen($back['url']) <= 12000 && (time() - (int) ($back['at'] ?? 0)) <= self::CANCEL_BACK_MAX_AGE) {
            session(['Booking-Error-Msz' => self::CANCEL_BACK_MESSAGE]);
            Log::info('FARESHUNT_STRIPE_CANCEL_BACK_TO_BOOKING', [
                'attempt_id' => (int) ($back['attempt_id'] ?? 0),
                'flow' => str_contains($back['url'], '/flights-payment') ? 'fareleaders' : 'website',
            ]);

            return redirect()->to($back['url'])->header('Cache-Control', 'no-store');
        }
        Log::info('FARESHUNT_STRIPE_CANCEL_NO_BACK', ['reason' => is_array($back) ? 'invalid_or_old' : 'none']);

        return $this->page(
            'Payment not completed',
            'No money has been taken from your card. You can go back to your booking and try again.'
        );
    }

    public function status(string $reference): JsonResponse
    {
        $s = $this->statusOr404($reference);

        return response()->json($s)->header('Cache-Control', 'no-store')->header('X-Robots-Tag', 'noindex');
    }

    private function statusOr404(string $reference): array
    {
        $s = app(StripeReturnStatusService::class)->forReference($reference);
        if ($s === null) {
            abort(404);
        }

        return $s;
    }

    private function page(string $title, string $message, ?string $reference = null, ?array $s = null): Response
    {
        $t = e($title);
        $m = e($message);
        $home = e(url('/'));
        $link = ($s && $s['booking_url']) ? '<p><a id="fhBookingLink" href="'.e($s['booking_url']).'">View your booking</a></p>' : '<p><a id="fhBookingLink" href="#" style="display:none">View your booking</a></p>';
        $poll = '';

        if ($reference !== null && $s !== null && !$s['final']) {
            $url = json_encode(route('stripe.test.return.status', ['reference' => $reference]), JSON_UNESCAPED_SLASHES);
            $poll = '<script>(function(){var n=0,u='.$url.';'
                .'function show(d){document.getElementById("fhTitle").textContent=d.title;document.getElementById("fhMsg").textContent=d.message;'
                .'var l=document.getElementById("fhBookingLink");if(d.booking_url){l.href=d.booking_url;l.style.display="inline";if(d.state==="confirmed"){setTimeout(function(){window.location.href=d.booking_url;},1500);}}}'
                .'function tick(){n++;fetch(u,{headers:{"Accept":"application/json"},cache:"no-store"}).then(function(r){return r.ok?r.json():null}).then(function(d){'
                .'if(d){show(d);if(d.final){document.getElementById("fhSpin").style.display="none";return;}}'
                .'if(n<75){setTimeout(tick,4000);}else{document.getElementById("fhSpin").style.display="none";'
                .'document.getElementById("fhMsg").textContent="This is taking longer than usual. We will email you as soon as your booking is confirmed. Please do not pay again.";}'
                .'}).catch(function(){if(n<75)setTimeout(tick,6000);});}setTimeout(tick,3000);})();</script>';
        }
        $spin = $poll !== '' ? '<p id="fhSpin" style="color:#64748b">Checking status&hellip;</p>' : '<p id="fhSpin" style="display:none"></p>';

        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width,initial-scale=1">'
            .'<meta name="robots" content="noindex,nofollow"><title>'.$t.' | Fares Hunt</title>'
            .'<style>body{font-family:system-ui,Arial,sans-serif;background:#f5f7fb;margin:0;padding:48px 16px;color:#1d2433}'
            .'.box{max-width:560px;margin:0 auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 4px 18px rgba(0,0,0,.06)}'
            .'h1{font-size:22px;margin:0 0 12px}p{line-height:1.6;margin:0 0 20px}a{color:#0b5cff}</style></head>'
            .'<body><div class="box"><h1 id="fhTitle">'.$t.'</h1><p id="fhMsg">'.$m.'</p>'.$spin.$link
            .'<a href="'.$home.'">Back to Fares Hunt</a></div>'.$poll.'</body></html>';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
