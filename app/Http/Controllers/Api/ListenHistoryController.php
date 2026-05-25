<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ListenHistory;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListenHistoryController extends Controller
{
    /**
     * Track a song play for the current user.
     */
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'song_id' => 'required|integer|exists:songs,id',
            'duration_listened' => 'nullable|integer|min:0',
            'completed' => 'nullable|boolean',
        ]);

        $user = $request->user();

        $listen = ListenHistory::firstOrNew([
            'user_id' => $user->id,
            'song_id' => $validated['song_id'],
        ]);

        $listen->play_count = ($listen->play_count ?? 0) + 1;
        $listen->duration_listened = $validated['duration_listened'] ?? $listen->duration_listened ?? 0;
        $listen->completed = $validated['completed'] ?? $listen->completed ?? false;
        $listen->save();

        return response()->json([
            'message' => 'Listen history tracked successfully',
            'data' => $listen,
        ]);
    }

    /**
     * Get current user's listen history.
     */
    public function myHistory(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);

        $history = ListenHistory::query()
            ->where('user_id', $request->user()->id)
            ->with('song.album')
            ->latest()
            ->paginate($perPage);

        return response()->json($history);
    }

    /**
     * Get most played songs globally.
     */
    public function mostPlayedSongs(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 25);

        $rows = ListenHistory::query()
            ->select('song_id', DB::raw('SUM(play_count) as play_count'))
            ->groupBy('song_id')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get();

        $songIds = $rows->pluck('song_id')->all();

        $songs = Song::query()
            ->with('album')
            ->whereIn('id', $songIds)
            ->get()
            ->keyBy('id');

        $data = $rows->map(function ($row) use ($songs) {
            $song = $songs->get($row->song_id);

            return [
                'song_id' => $row->song_id,
                'play_count' => (int) $row->play_count,
                'song' => $song,
            ];
        })->filter(fn ($item) => $item['song'] !== null)->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Get most played albums globally.
     */
    public function mostPlayedAlbums(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);

        $rows = ListenHistory::query()
            ->join('songs', 'songs.id', '=', 'user_listen_history.song_id')
            ->select('songs.album_id', DB::raw('SUM(user_listen_history.play_count) as play_count'))
            ->whereNotNull('songs.album_id')
            ->groupBy('songs.album_id')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get();

        $albumIds = $rows->pluck('album_id')->all();

        $albums = \App\Models\Album::query()
            ->whereIn('id', $albumIds)
            ->get()
            ->keyBy('id');

        $data = $rows->map(function ($row) use ($albums) {
            $album = $albums->get($row->album_id);

            return [
                'album_id' => $row->album_id,
                'play_count' => (int) $row->play_count,
                'album' => $album,
            ];
        })->filter(fn ($item) => $item['album'] !== null)->values();

        return response()->json([
            'data' => $data,
        ]);
    }
}
