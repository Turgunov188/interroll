<?php

use App\Enums\Locale;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\SiteController;
use App\Http\Middleware\HoneypotTrap;
use Illuminate\Support\Facades\Route;

// Old URLs (Uzbek words, the separate Russian homepage) → current ones, permanently.
// Registered first, so /blog/{slug} does not swallow /blog/shore-a.
foreach (Locale::cases() as $locale) {
    foreach (config('site.redirects') as $from => $to) {
        if ($from === '/ru' && ! $locale->isMain()) {
            continue;
        }

        Route::permanentRedirect($locale->path($from), $locale->path($to));
    }
}

/*
| Public site in every language. Russian lives at the root (/catalog),
| the others get a prefix (/uz/catalog). Page list: config/site.php.
*/
foreach (Locale::cases() as $locale) {
    $name = "site.{$locale->value}";

    foreach (config('site.pages') as $uri => $page) {
        Route::get($locale->path($uri), [SiteController::class, 'show'])
            ->defaults('page', $page)
            ->defaults('lang', $locale->value)
            ->name("{$name}.{$page}");
    }

    $productUri = config('site.product.uri');

    Route::get($locale->path($productUri.'/{slug}'), [ProductPageController::class, 'show'])
        ->defaults('lang', $locale->value)
        ->where('slug', '[a-z0-9-]+')
        ->name("{$name}.product");

    Route::get($locale->path(config('site.blog.uri').'/{slug}'), [BlogPostController::class, 'show'])
        ->defaults('lang', $locale->value)
        ->where('slug', '[a-z0-9-]+')
        ->name("{$name}.post");

    Route::get($locale->path($productUri), [ProductPageController::class, 'defaultProduct'])
        ->defaults('lang', $locale->value)
        ->name("{$name}.product.default");
}


// Every form on the site posts here → leads table → admin panel «Заявки».
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware([HoneypotTrap::class, 'throttle:10,1'])
    ->name('leads.store');
