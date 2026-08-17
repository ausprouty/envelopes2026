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

    public function create(): Response
    {
        return Inertia::render('admin/users/Create', [
            'households' => Household::where('is_active', true)
                ->orderBy('household_name')
                ->get(),
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        $user->households()->detach();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/Edit', [
            'user' => $user->load('households'),
            'households' => Household::where('is_active', true)
                ->orderBy('household_name')
                ->get(),
        ]);
    }

    public function index(): Response
    {
        return Inertia::render('admin/users/Index', [
            'users' => User::with('households')->orderBy('name')->get(),
            'households' => Household::where('is_active', true)
                ->orderBy('household_name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'household_id' => ['required', 'exists:households,id'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => bcrypt(str()->random(32)),
        ]);

        $user->households()->attach(
            $validated['household_id'],
            ['role' => 'member']
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'household_id' => ['required', 'exists:households,id'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        $user->households()->sync([
            $validated['household_id'] => [
                'role' => 'member',
            ],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function updateHousehold(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'household_id' => ['required', 'exists:households,id'],
            'household_role' => ['required', 'in:member,coach'],
        ]);

        $user->households()->sync([
            $validated['household_id'] => [
                'role' => $validated['household_role'],
            ],
        ]);

        return back();
    }
}
