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

        $search = $request->input('search');

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /*
     * User creation is disabled per requirements
    public function create(): Response
    {
        $this->authorize('create', \App\Models\User::class);
        return Inertia::render('Admin/Users/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\User::class);
        // ...
    }
    */

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

    public function ban(User $user)
    {
        $this->authorize('update', $user);
        $user->update(['is_banned' => true]);

        return back()->with('success', 'User banned');
    }

    public function unban(User $user)
    {
        $this->authorize('update', $user);
        $user->update(['is_banned' => false]);

        return back()->with('success', 'User unbanned');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();

        return back()->with('success', 'User removed');
    }
}
