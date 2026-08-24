<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\DeactivateUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('admin/users/Index', [
            'users' => User::with('roles')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('admin/users/Create');
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:admin,recruiter,hiring_manager,interviewer'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        foreach ($validated['roles'] as $role) {
            $user->roles()->create(['role' => $role]);
        }

        $activityLogger->log(
            action: 'user.created',
            subject: $user,
            metadata: ['roles' => $validated['roles']],
        );

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/users/Edit', [
            'user' => $user->load('roles'),
        ]);
    }

    public function update(Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:admin,recruiter,hiring_manager,interviewer'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->roles()->delete();
        foreach ($validated['roles'] as $role) {
            $user->roles()->create(['role' => $role]);
        }

        $activityLogger->log(
            action: 'user.updated',
            subject: $user,
            metadata: ['roles' => $validated['roles']],
        );

        return redirect()->route('admin.users.index');
    }

    public function deactivate(User $user, DeactivateUser $deactivateUser): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        /** @var User $actor */
        $actor = auth()->user();

        $result = $deactivateUser->handle($user, $actor);

        return redirect()
            ->route('admin.users.index')
            ->with('warnings', $result['warnings']);
    }
}
