<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ListenHistory;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Mobile-only artist dashboard endpoints (MB prefix).
 */
class MbArtistDashboardController extends Controller
{
    /**
     * Top listened songs for the authenticated artist (listen history + song play_count).
     */
    public function topListenedSongs(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json([
                'message' => 'Artist profile not found for this account.',
            ], 403);
        }

        $limit = min(max((int) $request->get('limit', 6), 1), 20);

        $listenScores = ListenHistory::query()
            ->join('songs', 'songs.id', '=', 'user_listen_history.song_id')
            ->where('songs.artist_id', $artist->id)
            ->where('songs.is_active', true)
            ->groupBy('user_listen_history.song_id')
            ->select('user_listen_history.song_id', DB::raw('SUM(user_listen_history.play_count) as listen_count'))
            ->pluck('listen_count', 'song_id');

        $songs = Song::query()
            ->where('artist_id', $artist->id)
            ->where('is_active', true)
            ->with(['album', 'artistModel'])
            ->get()
            ->map(function (Song $song) use ($listenScores) {
                $listenCount = (int) ($listenScores[$song->id] ?? 0);
                $streamCount = (int) ($song->play_count ?? 0);
                $totalPlays = $listenCount + $streamCount;

                return [
                    'song' => $song,
                    'listen_count' => $listenCount,
                    'stream_count' => $streamCount,
                    'play_count' => $totalPlays,
                ];
            })
            ->filter(fn (array $row) => $row['play_count'] > 0)
            ->sortByDesc('play_count')
            ->take($limit)
            ->values();

        return response()->json([
            'data' => $songs->map(function (array $row) {
                /** @var Song $song */
                $song = $row['song'];

                return [
                    'song' => MbSongTransformer::transform($song),
                    'listen_count' => (int) $row['listen_count'],
                    'stream_count' => (int) $row['stream_count'],
                    'play_count' => (int) $row['play_count'],
                ];
            })->all(),
        ]);
    }
}
