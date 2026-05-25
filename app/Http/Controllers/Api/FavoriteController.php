<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Favorite;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FavoriteController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get all user favorites (songs and albums).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->get('type', null); // 'song' or 'album' or null for all

        $query = $user->favorites();

        if ($type === 'song') {
            $query->where('favoritable_type', Song::class);
        } elseif ($type === 'album') {
            $query->where('favoritable_type', Album::class);
        }

        $favorites = $query->with('favoritable')->latest()->paginate(20);

        return response()->json($favorites);
    }

    /**
     * Add a song to favorites.
     */
    public function addSong(Request $request): JsonResponse
    {
        $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $user = $request->user();
        $song = Song::findOrFail($request->song_id);

        // Check if already favorited
        $existing = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $song->id)
            ->where('favoritable_type', Song::class)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Song already in favorites',
            ], 409);
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'favoritable_id' => $song->id,
            'favoritable_type' => Song::class,
        ]);

        return response()->json([
            'message' => 'Song added to favorites',
            'data' => $favorite->load('favoritable'),
        ], 201);
    }

    /**
     * Add an album to favorites.
     */
    public function addAlbum(Request $request): JsonResponse
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id',
        ]);

        $user = $request->user();
        $album = Album::findOrFail($request->album_id);

        // Check if already favorited
        $existing = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $album->id)
            ->where('favoritable_type', Album::class)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Album already in favorites',
            ], 409);
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'favoritable_id' => $album->id,
            'favoritable_type' => Album::class,
        ]);

        return response()->json([
            'message' => 'Album added to favorites',
            'data' => $favorite->load('favoritable'),
        ], 201);
    }

    /**
     * Remove a favorite (song or album).
     */
    public function destroy(Favorite $favorite): JsonResponse
    {
        $this->authorize('delete', $favorite);

        $favorite->delete();

        return response()->json([
            'message' => 'Favorite removed successfully',
        ]);
    }

    /**
     * Remove a song from favorites.
     */
    public function removeSong(Request $request): JsonResponse
    {
        $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $user = $request->user();
        $song = Song::findOrFail($request->song_id);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $song->id)
            ->where('favoritable_type', Song::class)
            ->first();

        if (! $favorite) {
            return response()->json([
                'message' => 'Favorite not found',
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Song removed from favorites',
        ]);
    }

    /**
     * Remove an album from favorites.
     */
    public function removeAlbum(Request $request): JsonResponse
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id',
        ]);

        $user = $request->user();
        $album = Album::findOrFail($request->album_id);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $album->id)
            ->where('favoritable_type', Album::class)
            ->first();

        if (! $favorite) {
            return response()->json([
                'message' => 'Favorite not found',
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Album removed from favorites',
        ]);
    }

    /**
     * Check if a song is favorited.
     */
    public function checkSong(Request $request): JsonResponse
    {
        $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $user = $request->user();
        $isFavorited = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $request->song_id)
            ->where('favoritable_type', Song::class)
            ->exists();

        return response()->json([
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * Check if an album is favorited.
     */
    public function checkAlbum(Request $request): JsonResponse
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id',
        ]);

        $user = $request->user();
        $isFavorited = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $request->album_id)
            ->where('favoritable_type', Album::class)
            ->exists();

        return response()->json([
            'is_favorited' => $isFavorited,
        ]);
    }
}
