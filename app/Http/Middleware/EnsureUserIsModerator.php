<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsModerator
{
    /**
     * Consente l'accesso solo agli utenti moderatori
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_moderator) {
            abort(403, 'Accesso riservato ai moderatori');
        }

        return $next($request);
    }
}
