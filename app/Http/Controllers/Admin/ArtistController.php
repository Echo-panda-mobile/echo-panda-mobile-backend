<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\User;
use App\Services\FirebaseUserProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ArtistController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\Artist::class);
        $artists = Artist::latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Artists/Index', [
            'artists' => $artists,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', \App\Models\Artist::class);

        return Inertia::render('Admin/Artists/Create', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Artist::class);

        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id', 'unique:artists,user_id'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'user_role' => ['nullable', Rule::in([\App\Models\User::ROLE_USER, \App\Models\User::ROLE_ARTIST, \App\Models\User::ROLE_PUBLICER, \App\Models\User::ROLE_ADMIN])],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', 'unique:artists,slug'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'verification_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'verification_reason' => ['nullable', 'string', 'max:5000'],
        ]);

        $firebaseProvisioner = app(FirebaseUserProvisioner::class);
        $createdUser = null;
        $shouldSendInvite = false;

        try {
            $artist = DB::transaction(function () use ($request, $validated, $firebaseProvisioner, &$createdUser, &$shouldSendInvite) {
                $user = null;

                if (! empty($validated['user_id'])) {
                    $user = User::query()->findOrFail($validated['user_id']);
                } elseif (! empty($validated['email'])) {
                    $userData = [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'role' => $validated['user_role'] ?? User::ROLE_ARTIST,
                    ];

                    if (Schema::hasColumn('users', 'is_banned')) {
                        $userData['is_banned'] = false;
                    }

                    $user = User::create($userData);
                    $createdUser = $user;
                    $shouldSendInvite = true;
                }

                if ($user) {
                    if (! $user->firebase_uid) {
                        $shouldSendInvite = true;
                    }

                    $provisionResult = $firebaseProvisioner->provision($user);

                    if (($provisionResult['firebase_uid'] ?? null) && $user->firebase_uid !== $provisionResult['firebase_uid']) {
                        $user->forceFill(['firebase_uid' => $provisionResult['firebase_uid']])->save();
                    }
                }

                $baseSlug = Str::slug($validated['slug'] ?? $validated['name']);
                $slug = $baseSlug;
                $counter = 2;

                while (Artist::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter++;
                }

                return Artist::create([
                    'user_id' => $user?->id,
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'bio' => $validated['bio'] ?? null,
                    'is_active' => $request->boolean('is_active', true),
                    'verification_status' => $validated['verification_status'],
                    'verification_reason' => $validated['verification_reason'] ?? null,
                    'verified_at' => $validated['verification_status'] === 'approved' ? now() : null,
                ]);
            });
        } catch (\Throwable $throwable) {
            if ($createdUser && $createdUser->firebase_uid) {
                $firebaseProvisioner->deleteByUid($createdUser->firebase_uid);
            }

            if ($createdUser) {
                $createdUser->delete();
            }

            throw $throwable;
        }

        if ($shouldSendInvite && $artist->user) {
            $firebaseProvisioner->sendInvite($artist->user->fresh());
        }

        return redirect()->route('admin.artists.show', $artist)->with('success', 'Artist created');
    }

    public function show(Artist $artist): Response
    {
        $this->authorize('view', $artist);
        $artist->load(['songs' => fn ($query) => $query->orderByDesc('play_count')->limit(12), 'albums', 'user.followers']);
        $artist->loadCount(['songs', 'albums']);
        $artist->setAttribute('followers_count', $artist->user?->followers()->count() ?? 0);

        return Inertia::render('Admin/Artists/Show', [
            'artist' => $artist,
        ]);
    }

    public function edit(Artist $artist): Response
    {
        $this->authorize('update', $artist);
        $artist->loadMissing('user');

        return Inertia::render('Admin/Artists/Edit', ['artist' => $artist]);
    }

    public function update(Request $request, Artist $artist)
    {
        $this->authorize('update', $artist);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'verification_status' => ['sometimes', 'required', Rule::in(['pending', 'approved', 'rejected'])],
            'verification_reason' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        if (array_key_exists('verification_status', $validated)) {
            $validated['verified_at'] = $validated['verification_status'] === 'approved' ? now() : null;
        }

        $artist->update($validated);

        if ($artist->user) {
            $userUpdates = [];

            if (array_key_exists('name', $validated) && $artist->user->name !== $validated['name']) {
                $userUpdates['name'] = $validated['name'];
            }

            if ($userUpdates) {
                $artist->user->update($userUpdates);
            }

            app(FirebaseUserProvisioner::class)->provision($artist->user->fresh());
        }

        return back()->with('success', 'Artist updated');
    }

    public function destroy(Artist $artist)
    {
        $this->authorize('delete', $artist);

        if ($artist->user) {
            app(FirebaseUserProvisioner::class)->deleteByUid($artist->user->firebase_uid);
            $artist->user->delete();
        }

        $artist->delete();

        return redirect()->route('admin.artists.index')->with('success', 'Artist deleted');
    }
}
