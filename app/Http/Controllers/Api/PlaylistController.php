<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    /**
     * List current user's playlists.
     */
    public function index(Request $request): JsonResponse
    {
        $playlists = Playlist::query()
            ->where('user_id', $request->user()->id)
            ->withCount('songs')
            ->latest()
            ->get();

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
        ]);

        $playlist = Playlist::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Playlist created successfully',
            'data' => $playlist,
        ], 201);
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
