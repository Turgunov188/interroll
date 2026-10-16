<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/** The admin panel always runs in Russian (Filament buttons, dates, messages). */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale(Locale::Ru->value);
        Carbon::setLocale(Locale::Ru->value);

        return $next($request);
    }
}
