<?php

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
        // Hosting panels (DirectAdmin, cPanel) often sit behind a proxy: trust it so https:// links stay https://
        $middleware->trustProxies(at: '*');
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
