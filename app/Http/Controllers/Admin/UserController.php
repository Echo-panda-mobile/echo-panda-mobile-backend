<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\User::class);
        $users = User::query()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', \App\Models\User::class);

        return Inertia::render('Admin/Users/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:user,artist,publicer,admin'],
            'is_banned' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_banned' => $request->boolean('is_banned', false),
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'User created');
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->loadCount(['favorites', 'listenHistory', 'following', 'followers']);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['sometimes', 'required', 'in:user,artist,publicer,admin'],
            'is_banned' => ['sometimes', 'boolean'],
        ]);

        $user->update($validated);

        return back()->with('success', 'User updated');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();

        return back()->with('success', 'User removed');
    }
}
