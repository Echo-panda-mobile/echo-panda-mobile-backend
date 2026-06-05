<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    protected function resolveImageUrl(?string $imageSource): ?string
    {
        if (! $imageSource) {
            return null;
        }

        if (preg_match('#^https?://#i', $imageSource)) {
            // If it's already a full URL (like from S3 public url),
            // but we are getting 403, we should try to treat it as a key if it's our bucket
            if (str_contains($imageSource, 'amazonaws.com')) {
                $path = parse_url($imageSource, PHP_URL_PATH);
                $imageSource = ltrim($path, '/');
            } else {
                return $imageSource;
            }
        }

        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl(ltrim($imageSource, '/'), now()->addMinutes(60));
        } catch (\Exception $e) {
            return $disk->url($imageSource);
        }
    }

    protected function transformPlaylist(Playlist $playlist): array
    {
        return [
            'id' => $playlist->id,
            'name' => $playlist->name,
            'description' => $playlist->description,
            'image_url' => $this->resolveImageUrl($playlist->image_url),
            'songs_count' => $playlist->songs_count ?? $playlist->songs()->count(),
            'created_at' => $playlist->created_at,
            'updated_at' => $playlist->updated_at,
        ];
    }

    /**
     * List current user's playlists.
     */
    public function index(Request $request): JsonResponse
    {
        $playlists = Playlist::query()
            ->where('user_id', $request->user()->id)
            ->withCount('songs')
            ->latest()
            ->get()
            ->map(fn($p) => $this->transformPlaylist($p));

        return response()->json([
            'data' => $playlists,
        ]);
    }

    /**
     * Create a playlist.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:2048',
        ]);

        $playlist = Playlist::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        return response()->json([
            'message' => 'Playlist created successfully',
            'data' => $this->transformPlaylist($playlist),
        ], 201);
    }

    /**
     * Update a playlist.
     */
    public function update(Request $request, Playlist $playlist): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:2048',
        ]);

        $playlist->update($validated);

        return response()->json([
            'message' => 'Playlist updated successfully',
            'data' => $this->transformPlaylist($playlist),
        ]);
    }

    /**
     * Delete a playlist owned by current user.
     */
    public function destroy(Request $request, Playlist $playlist): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $playlist->delete();

        return response()->json([
            'message' => 'Playlist deleted successfully',
        ]);
    }

    /**
     * Get songs in a playlist.
     */
    public function songs(Request $request, Playlist $playlist): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $songs = $playlist->songs()
            ->with('album')
            ->orderByDesc('playlist_song.added_at')
            ->get();

        return response()->json([
            'data' => $songs,
        ]);
    }

    /**
     * Add song to playlist.
     */
    public function addSong(Request $request, Playlist $playlist): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'song_id' => 'required|integer|exists:songs,id',
        ]);

        $songId = (int) $validated['song_id'];

        if ($playlist->songs()->where('song_id', $songId)->exists()) {
            return response()->json([
                'message' => 'Song already in playlist',
            ], 409);
        }

        $playlist->songs()->attach($songId, ['added_at' => now()]);

        return response()->json([
            'message' => 'Song added to playlist',
        ], 201);
    }

    /**
     * Remove song from playlist.
     */
    public function removeSong(Request $request, Playlist $playlist, Song $song): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $playlist->songs()->detach($song->id);

        return response()->json([
            'message' => 'Song removed from playlist',
        ]);
    }

    /**
     * Check if song exists in playlist.
     */
    public function hasSong(Request $request, Playlist $playlist, Song $song): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $exists = $playlist->songs()->where('song_id', $song->id)->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }
}
