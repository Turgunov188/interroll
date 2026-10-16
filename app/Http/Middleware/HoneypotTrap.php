<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bots fill every field, people never see the hidden "website" field.
 * A filled trap gets a fake success so the bot does not retry.
 */
class HoneypotTrap
{
    public const FIELD = 'website';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled(self::FIELD)) {
            return response()->json(['ok' => true]);
        }

        return $next($request);
    }
}
