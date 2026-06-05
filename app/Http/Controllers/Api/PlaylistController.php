<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaylistController extends Controller
{
    protected function resolveCoverUrl(?string $coverSource): ?string
    {
        if (! $coverSource) {
            return null;
        }

        if (preg_match('#^https?://#i', $coverSource)) {
            return $coverSource;
        }

        /** @var mixed $disk */
        $disk = Storage::disk('s3');

        if (method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl(ltrim($coverSource, '/'), now()->addMinutes(60));
        }

        return $disk->url(ltrim($coverSource, '/'));
    }

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

    protected function transformPlaylist(Playlist $playlist): array
    {
        return [
            'id' => $playlist->id,
            'name' => $playlist->name,
            'description' => $playlist->description,
            'cover_key' => $playlist->cover_key,
            'image_url' => $this->resolveCoverUrl($playlist->cover_key),
            'songs_count' => $playlist->songs_count ?? null,
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
            ->map(fn (Playlist $playlist) => $this->transformPlaylist($playlist));

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
            'description' => 'nullable|string|max:2000',
            'image_url' => 'nullable|string|max:2048',
            'cover_key' => 'nullable|string|max:512',
        ]);

        $coverKey = $validated['cover_key'] ?? $this->keyFromUrlOrKey($validated['image_url'] ?? null);

        $playlist = Playlist::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'cover_key' => $coverKey,
        ]);

        $playlist->loadCount('songs');

        return response()->json([
            'message' => 'Playlist created successfully',
            'data' => $this->transformPlaylist($playlist),
        ], 201);
    }

    /**
     * Update a playlist owned by the current user.
     */
    public function update(Request $request, Playlist $playlist): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image_url' => 'nullable|string|max:2048',
            'cover_key' => 'nullable|string|max:512',
        ]);

        $payload = [];

        if (array_key_exists('name', $validated)) {
            $payload['name'] = $validated['name'];
        }

        if (array_key_exists('description', $validated)) {
            $payload['description'] = $validated['description'];
        }

        if (array_key_exists('cover_key', $validated)) {
            $payload['cover_key'] = $validated['cover_key'];
        } elseif (array_key_exists('image_url', $validated)) {
            $payload['cover_key'] = $this->keyFromUrlOrKey($validated['image_url']);
        }

        $playlist->update($payload);
        $playlist->loadCount('songs');

        return response()->json([
            'message' => 'Playlist updated successfully',
            'data' => $this->transformPlaylist($playlist->fresh()),
        ]);
    }

    /**
     * Upload or replace a playlist cover image.
     */
    public function uploadCover(Request $request, Playlist $playlist): JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'file' => 'required|image|max:5120',
        ]);

        $file = $validated['file'];
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $uuid = (string) Str::uuid();
        $key = "images/playlist-covers/user-{$request->user()->id}/{$uuid}.{$extension}";

        Storage::disk('s3')->put($key, fopen($file->getRealPath(), 'r'));

        $playlist->update(['cover_key' => $key]);
        $playlist->loadCount('songs');

        return response()->json([
            'message' => 'Playlist cover updated successfully',
            'data' => $this->transformPlaylist($playlist->fresh()),
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
