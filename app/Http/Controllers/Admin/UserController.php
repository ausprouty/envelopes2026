<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/users/Index', [
            'users' => User::with('households')->orderBy('name')->get(),
            'households' => Household::where('is_active', true)
                ->orderBy('household_name')
                ->get(),
        ]);
    }

    public function updateHousehold(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'household_id' => ['required', 'exists:households,id'],
        ]);

        $user->households()->sync([
            $validated['household_id'] => [
                'role' => 'member',
            ],
        ]);

        return back();
    }
}
