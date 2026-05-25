<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $artist = $user->artist;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'artist_id' => $artist?->id,
                'artist' => $artist ? [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'image_url' => $artist->image_url,
                ] : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $imageUrl = $validated['image_url'] ?? null;
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

        if ($user->artist && $imageUrl !== null) {
            $user->artist->update([
                'image_url' => $imageUrl,
            ]);
        }

        $artist = $user->artist;

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'artist_id' => $artist?->id,
                'artist' => $artist ? [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'image_url' => $artist->image_url,
                ] : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
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
