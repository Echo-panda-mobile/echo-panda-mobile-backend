<?php

namespace App\Http\Controllers\Api\Artist;

use App\Http\Controllers\Controller;
use App\Models\ListenHistory;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json([
                'message' => 'Artist profile not found for this account.',
            ], 403);
        }

        $songIds = Song::query()
            ->where('artist_id', $artist->id)
            ->pluck('id');

        if ($songIds->isEmpty()) {
            return response()->json([
                'artist_id' => $artist->id,
                'monthly_streams' => 0,
                'top_song' => null,
                'listener_countries' => [],
            ]);
        }

        $monthlyStreams = (int) ListenHistory::query()
            ->whereIn('song_id', $songIds)
            ->sum('play_count');

        $topSongRow = ListenHistory::query()
            ->select('song_id', DB::raw('SUM(play_count) as play_count'))
            ->whereIn('song_id', $songIds)
            ->groupBy('song_id')
            ->orderByDesc('play_count')
            ->first();

        $topSong = null;
        if ($topSongRow) {
            $song = Song::query()->find($topSongRow->song_id);
            if ($song) {
                $topSong = [
                    'id' => $song->id,
                    'title' => $song->title,
                    'play_count' => (int) $topSongRow->play_count,
                ];
            }
        }

        // Placeholder until geolocation tracking is added to stream/listen events.
        $listenerCountries = [];

        return response()->json([
            'artist_id' => $artist->id,
            'monthly_streams' => $monthlyStreams,
            'top_song' => $topSong,
            'listener_countries' => $listenerCountries,
        ]);
    }
}
