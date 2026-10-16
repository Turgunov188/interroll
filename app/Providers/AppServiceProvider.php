<?php

namespace App\Providers;

use App\Services\Site\SitePageRenderer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SitePageRenderer::class, fn ($app) => new SitePageRenderer(
            $app->make(Filesystem::class),
            resource_path(config('site.directory', 'site')),
        ));
    }

    public function boot(): void
    {
        //
    }
}
