<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/stripe_test_checkout.php'));

            // Stripe webhook (Step E): no web/session/CSRF middleware.
            Route::middleware('throttle:120,1,stripe-webhook')
                ->group(base_path('routes/stripe_test_webhook.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if ($request->is('api/live-pax/*')) {
                return Limit::perMinute(1500)->by('live-pax:'.$request->ip());
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(20)->by('contact:'.$request->ip());
        });

        // CRM login brute-force protection. Applied to both login routes
        // (legacy /crm/login and crm.fareshunt.com/login). When a limit is hit
        // the user is sent back to the login form with a normal error message
        // instead of a bare 429 page.
        RateLimiter::for('crm-login', function (Request $request) {
            $username = strtolower(trim((string) $request->input('username')));
            $tooMany = function (Request $request) {
                return back()
                    ->withErrors(['username' => 'Too many login attempts. Please wait a minute and try again.'])
                    ->withInput($request->only('username'));
            };

            return [
                // One account from one IP: 5 tries per minute.
                Limit::perMinute(5)->by('crm-login-user:'.$username.'|'.$request->ip())->response($tooMany),
                // One IP across all usernames: 20 per minute (office with shared IP stays usable).
                Limit::perMinute(20)->by('crm-login-ip:'.$request->ip())->response($tooMany),
            ];
        });

        // SEC-007: every request below makes a paid, slow call to the flight
        // supplier. Limits are per visitor IP and sized for real users.
        //
        // Search flow: POST /submit-search + GET /flight-listing/{code} = 2 hits
        // per search, so 30/min = about 15 searches a minute from one IP.
        // /fareleader_ui_search and /api/search share the same bucket.
        RateLimiter::for('flight-search', function (Request $request) {
            return Limit::perMinute(30)->by('flight-search:'.$request->ip());
        });

        // Homepage / deals live fare cards (/ajax/route-fare, /ajax/get-topflight-price).
        // Each page load asks once per "live" card, max 4 in parallel.
        RateLimiter::for('route-fare', function (Request $request) {
            return Limit::perMinute(90)->by('route-fare:'.$request->ip());
        });

        // FareLeader meta JSON search. Called server-to-server by the meta
        // partner, so partner IPs listed in FARELEADER_ALLOWED_IPS are not limited.
        RateLimiter::for('meta-search', function (Request $request) {
            $allowed = (array) config('services.fareleader.allowed_ips', []);
            if (in_array($request->ip(), $allowed, true)) {
                return Limit::none();
            }

            return Limit::perMinute(60)->by('meta-search:'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json(['error' => 'Too many requests'], 429, $headers);
                });
        });
    }
}