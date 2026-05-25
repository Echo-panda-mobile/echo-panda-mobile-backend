<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\User::class);
        $users = User::latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);
        return Inertia::render('Admin/Users/Show', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $user->update($request->only(['role', 'active', 'banned']));

        return back()->with('success', 'User updated');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();

        return back()->with('success', 'User removed');
    }
}
