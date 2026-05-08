<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan user sudah memiliki keluarga.
 * Redirect ke halaman setup keluarga jika belum.
 */
class EnsureHasFamily
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user() &&
            !$request->user()->family_id &&
            !$request->routeIs('family.setup', 'family.store', 'family.accept-invitation')
        ) {
            return redirect()->route('family.setup');
        }

        return $next($request);
    }
}
