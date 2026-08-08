<?php

namespace App\Http\Middleware;

use App\Models\Household;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHouseholdAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $household = $request->route('household');

        if (! $household instanceof Household) {
            abort(404);
        }

        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Administrators may access all households.
        if ($user->role === 'admin') {
            return $next($request);
        }

        $hasAccess = $user->households()
            ->whereKey($household->id)
            ->exists();

        abort_unless($hasAccess, 403);

        return $next($request);
    }
}
