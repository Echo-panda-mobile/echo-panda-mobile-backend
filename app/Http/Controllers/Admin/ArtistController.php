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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ArtistController extends Controller
{
    protected function uploadImage($file, $folder, $artistName): string
    {
        $ext = $file->getClientOriginalExtension();
        $uuid = (string) Str::uuid();
        $artistSlug = Str::slug($artistName ?: 'artist');
        $key = trim($folder, '/')."/{$artistSlug}/{$uuid}.{$ext}";

        // Use the public disk for artist images so they are easily accessible
        // Fallback to S3 if public disk is not preferred or if we're in production
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

        \Illuminate\Support\Facades\Storage::disk($disk)->put($key, fopen($file->getRealPath(), 'r'));

        return $key;
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\Artist::class);
        $artists = Artist::latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Artists/Index', [
            'artists' => $artists,
            'users' => [], // Fallback for old builds
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', \App\Models\Artist::class);

        return Inertia::render('Admin/Artists/Create', [
            'users' => [], // Fallback for old builds
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Artist::class);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:200'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'profile_image' => ['nullable', 'image', 'max:5120'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $firebaseProvisioner = app(FirebaseUserProvisioner::class);
        $createdUser = null;

        try {
            $artist = DB::transaction(function () use ($request, $validated, $firebaseProvisioner, &$createdUser) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'role' => User::ROLE_ARTIST,
                ];

                if (Schema::hasColumn('users', 'is_banned')) {
                    $userData['is_banned'] = false;
                }

                $user = User::create($userData);
                $createdUser = $user;

                $provisionResult = $firebaseProvisioner->provision($user);
                if (($provisionResult['firebase_uid'] ?? null)) {
                    $user->forceFill(['firebase_uid' => $provisionResult['firebase_uid']])->save();
                }

                $baseSlug = Str::slug($validated['name']);
                $slug = $baseSlug;
                $counter = 2;
                while (Artist::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter++;
                }

                $profileKey = null;
                if ($request->hasFile('profile_image')) {
                    $profileKey = $this->uploadImage($request->file('profile_image'), 'images/artist-images', $validated['name']);
                }

                $coverKey = null;
                if ($request->hasFile('cover_image')) {
                    $coverKey = $this->uploadImage($request->file('cover_image'), 'images/artist-images', $validated['name']);
                }

                return Artist::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'bio' => $validated['bio'] ?? null,
                    'image_url' => $profileKey,
                    'cover_image_url' => $coverKey,
                    'facebook_url' => $validated['facebook_url'] ?? null,
                    'instagram_url' => $validated['instagram_url'] ?? null,
                    'tiktok_url' => $validated['tiktok_url'] ?? null,
                    'youtube_url' => $validated['youtube_url'] ?? null,
                    'is_active' => $request->boolean('is_active', true),
                    'verification_status' => 'approved',
                    'verified_at' => now(),
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

        if ($artist->user) {
            $firebaseProvisioner->sendInvite($artist->user->fresh());
        }

        return redirect()->route('admin.artists.show', $artist)->with('success', 'Artist created');
    }

    public function show(Artist $artist): Response
    {
        $this->authorize('view', $artist);

        $artist->load([
            'songs' => fn ($query) => $query->orderByDesc('play_count')->limit(12),
            'songs.album',
            'albums',
            'user'
        ]);

        $artist->loadCount(['songs', 'albums']);
        $artist->loadSum('songs', 'play_count');

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
            'profile_image' => ['nullable', 'image', 'max:5120'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'verification_status' => ['sometimes', 'required', Rule::in(['pending', 'approved', 'rejected'])],
            'verification_reason' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['image_url'] = $this->uploadImage($request->file('profile_image'), 'images/artist-images', $artist->name);
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_url'] = $this->uploadImage($request->file('cover_image'), 'images/artist-images', $artist->name);
        }

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
