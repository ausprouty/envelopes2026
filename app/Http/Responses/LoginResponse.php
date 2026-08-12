<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        $household = $user->households()
            ->orderBy('households.id')
            ->first();

        if (! $household) {
            return redirect('/');
        }

        return redirect(
            "/households/{$household->id}/dashboard"
        );
    }
}
