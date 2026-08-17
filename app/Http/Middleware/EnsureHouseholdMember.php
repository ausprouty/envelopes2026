<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHouseholdMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $householdRole = $request->attributes->get('household_role');

        if (! in_array($householdRole, ['admin', 'member'], true)) {
            abort(403);
        }

        return $next($request);
    }
}
