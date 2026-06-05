<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    protected function keyFromUrlOrKey(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            return ltrim($value, '/');
        }

        $path = parse_url($value, PHP_URL_PATH);

        return $path ? ltrim(rawurldecode($path), '/') : null;
    }

    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $artist = $user->artist;

        return response()->json([
            'user' => $this->serializeUser($user),
        ]);
    }

    protected function serializeUser($user): array
    {
        $artist = $user->artist;

        return [
            'id' => $user->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'image_url' => $user->image_url,
            'artist_id' => $artist?->id,
            'artist' => $artist ? [
                'id' => $artist->id,
                'name' => $artist->name,
                'image_url' => $artist->image_url,
            ] : null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    protected function assertUserOwnsImageKey($user, ?string $imageKey): void
    {
        if (! $imageKey) {
            return;
        }

        $userSlug = \Illuminate\Support\Str::slug($user->name ?: 'user-' . $user->id);
        $expectedPrefix = "images/user-images/{$userSlug}/";

        if (! str_starts_with($imageKey, $expectedPrefix)) {
            abort(422, 'The provided image does not belong to this user.');
        }

        if (! Storage::disk('s3')->exists($imageKey)) {
            abort(422, 'The uploaded image could not be found in storage.');
        }
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $imageKey = $this->keyFromUrlOrKey($validated['image_url'] ?? null);
        unset($validated['image_url']);

        if ($user->artist) {
            $this->authorize('update', $user->artist);
        }

        $user->update($validated);

        if ($user->artist && isset($validated['name'])) {
            $user->artist->update([
                'name' => $validated['name'],
            ]);
        }

        if ($imageKey !== null && $imageKey !== $user->storedImageKey()) {
            $this->assertUserOwnsImageKey($user, $imageKey);
            $user->deleteStoredImage();
            $user->update(['image_url' => $imageKey]);
        }

        $user->refresh();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $this->serializeUser($user),
        ]);
    }

    /**
     * Get user's favorite songs.
     */
    public function getFavoriteSongs(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 20);

        $favorites = $user->favorites()
            ->where('favoritable_type', 'App\Models\Song')
            ->with('favoritable.album')
            ->latest()
            ->paginate($perPage);

        return response()->json($favorites);
    }

    /**
     * Get user's favorite albums.
     */
    public function getFavoriteAlbums(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 20);

        $favorites = $user->favorites()
            ->where('favoritable_type', 'App\Models\Album')
            ->with('favoritable')
            ->latest()
            ->paginate($perPage);

        return response()->json($favorites);
    }
}
