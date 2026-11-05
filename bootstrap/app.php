<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hosting panels (DirectAdmin, cPanel) put nginx in front of Apache on the same machine: trust that
        // proxy so https:// links stay https://. Only local addresses, never '*': with '*' anyone could
        // send a fake X-Forwarded-For and get a fresh IP for every request, which voids the rate limits on
        // the forms and on the admin login. Another proxy (Cloudflare…) → TRUSTED_PROXIES in .env.
        $proxies = (string) env('TRUSTED_PROXIES', '127.0.0.1,::1,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16');
        $middleware->trustProxies(at: $proxies === '*' ? '*' : array_filter(array_map('trim', explode(',', $proxies))));

        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// On the hosting the web root is public_html next to the project folder (see deploy/README.md):
//   domains/<domain>/laravel      ← this project, without its public/ folder
//   domains/<domain>/public_html  ← the contents of public/
// Locally public/ exists and nothing changes.
if (! is_dir($app->basePath('public')) && is_dir($web = dirname($app->basePath()).'/public_html')) {
    $app->usePublicPath($web);
}

return $app;
