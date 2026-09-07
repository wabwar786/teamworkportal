<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict a route to a minimum owner tier.
 * Usage in routes:  ->middleware('tier:super')
 * Heads are allowed on tier:head; only the super owner on tier:super.
 */
class EnsureOwnerTier
{
    public function handle(Request $request, Closure $next, string $tier = 'head'): Response
    {
        $owner = $request->user();

        if (! $owner) {
            return redirect()->route('login');
        }

        if ($tier === 'super' && ! $owner->isSuper()) {
            abort(403, 'Only the super owner can access this.');
        }

        return $next($request);
    }
}
