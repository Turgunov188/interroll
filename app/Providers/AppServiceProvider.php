<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Site forms per visitor (IP): enough for a real customer, a wall for a bot filling the CRM and the disk. */
    public const LEADS_PER_MINUTE = 10;

    public const LEADS_PER_DAY = 40;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // The site is HTTPS-only: build https:// links even when the hosting's proxy is not among
        // the trusted ones (bootstrap/app.php), so pages never pull assets over plain http.
        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('leads', fn (Request $request) => [
            Limit::perMinute(self::LEADS_PER_MINUTE)->by('m:'.$request->ip()),
            Limit::perDay(self::LEADS_PER_DAY)->by('d:'.$request->ip()),
        ]);
    }
}
