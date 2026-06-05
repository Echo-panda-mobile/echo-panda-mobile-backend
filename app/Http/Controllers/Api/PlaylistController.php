<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    protected function hasPlaylistColumn(string $column): bool
    {
        static $cache = [];

        if (! array_key_exists($column, $cache)) {
            $cache[$column] = Schema::hasColumn('playlists', $column);
        }

        return (bool) $cache[$column];
    }

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

        /** @var FilesystemAdapter $disk */
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

        $payload = [
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
        ];

        if ($this->hasPlaylistColumn('description')) {
            $payload['description'] = $validated['description'] ?? null;
        }

        if ($this->hasPlaylistColumn('image_url')) {
            $payload['image_url'] = $validated['image_url'] ?? null;
        }

        $playlist = Playlist::create($payload);

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

        $updates = [];
        if (array_key_exists('name', $validated)) {
            $updates['name'] = $validated['name'];
        }
        if ($this->hasPlaylistColumn('description') && array_key_exists('description', $validated)) {
            $updates['description'] = $validated['description'];
        }
        if ($this->hasPlaylistColumn('image_url') && array_key_exists('image_url', $validated)) {
            $updates['image_url'] = $validated['image_url'];
        }

        if ($updates !== []) {
            $playlist->update($updates);
        }

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
    public function addSong(Request $request, Playlist $playlist, UserPreferenceService $preferenceService): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'song_id' => 'required|integer|exists:songs,id',
        ]);

        $songId = (int) $validated['song_id'];
        $song = Song::query()->with(['genre', 'artistModel', 'tag'])->findOrFail($songId);

        if ($playlist->songs()->where('song_id', $songId)->exists()) {
            return response()->json([
                'message' => 'Song already in playlist',
            ], 409);
        }

        $playlist->songs()->attach($songId, ['added_at' => now()]);
        $preferenceService->applyPlaylistAdd((int) $request->user()->id, $song);

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
