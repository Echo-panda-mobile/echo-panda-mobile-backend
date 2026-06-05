<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaylistController extends Controller
{
    protected function authorizeOwner(Request $request, Playlist $playlist): ?JsonResponse
    {
        if ((int) $playlist->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return null;
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
            ->map(fn (Playlist $playlist) => $playlist->toApiArray());

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

        $payload = [
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
        ];

        if (Playlist::hasColumn('description')) {
            $payload['description'] = $validated['description'] ?? null;
        }

        $coverPayload = array_filter([
            'cover_key' => $validated['cover_key'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        if ($coverPayload !== []) {
            (new Playlist)->applyCoverAttributes($coverPayload);
            $payload = array_merge($payload, $coverPayload);
        }

        $playlist = Playlist::create($payload);
        $playlist->loadCount('songs');

        return response()->json([
            'message' => 'Playlist created successfully',
            'data' => $playlist->toApiArray(),
        ], 201);
    }

    /**
     * Update a playlist owned by the current user.
     */
    public function update(Request $request, Playlist $playlist): JsonResponse
    {
        if ($response = $this->authorizeOwner($request, $playlist)) {
            return $response;
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

        if (Playlist::hasColumn('description') && array_key_exists('description', $validated)) {
            $payload['description'] = $validated['description'];
        }

        if (array_key_exists('cover_key', $validated) || array_key_exists('image_url', $validated)) {
            $coverPayload = [
                'cover_key' => $validated['cover_key'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
            ];
            (new Playlist)->applyCoverAttributes($coverPayload);
            $payload = array_merge($payload, $coverPayload);
        }

        if ($payload !== []) {
            $playlist->update($payload);
        }

        $playlist->loadCount('songs');

        return response()->json([
            'message' => 'Playlist updated successfully',
            'data' => $playlist->fresh()->toApiArray(),
        ]);
    }

    /**
     * Upload or replace a playlist cover image.
     */
    public function uploadCover(Request $request, Playlist $playlist): JsonResponse
    {
        if ($response = $this->authorizeOwner($request, $playlist)) {
            return $response;
        }

        $validated = $request->validate([
            'file' => 'required|image|max:5120',
        ]);

        $file = $validated['file'];
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $uuid = (string) Str::uuid();
        $key = "images/playlist-covers/user-{$request->user()->id}/{$uuid}.{$extension}";

        Storage::disk('s3')->put($key, fopen($file->getRealPath(), 'r'));

        $playlist->assignCoverKey($key);
        $playlist->save();
        $playlist->loadCount('songs');

        return response()->json([
            'message' => 'Playlist cover updated successfully',
            'data' => $playlist->fresh()->toApiArray(),
        ]);
    }

    /**
     * Delete a playlist owned by current user.
     */
    public function destroy(Request $request, Playlist $playlist): JsonResponse
    {
        if ($response = $this->authorizeOwner($request, $playlist)) {
            return $response;
        }

        Playlist::destroy($playlist->getKey());

        return response()->json([
            'message' => 'Playlist deleted successfully',
        ]);
    }

    /**
     * Get songs in a playlist.
     */
    public function songs(Request $request, Playlist $playlist): JsonResponse
    {
        if ($response = $this->authorizeOwner($request, $playlist)) {
            return $response;
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
        if ($response = $this->authorizeOwner($request, $playlist)) {
            return $response;
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
        if ($response = $this->authorizeOwner($request, $playlist)) {
            return $response;
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
        if ($response = $this->authorizeOwner($request, $playlist)) {
            return $response;
        }

        $exists = $playlist->songs()->where('song_id', $song->id)->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }
}
