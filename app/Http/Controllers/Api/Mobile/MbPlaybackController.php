<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ListenHistory;
use App\Models\PlayHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MbPlaybackController extends Controller
{
    /**
     * Mobile library: recently played (listen-history first, then play-history fallback).
     */
    public function recent(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $limit = min((int) $request->get('limit', 50), 100);

        $listenItems = ListenHistory::query()
            ->where('user_id', $userId)
            ->with(['song.album', 'song.artistModel'])
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        if ($listenItems->isNotEmpty()) {
            return response()->json([
                'data' => $listenItems->map(function (ListenHistory $item) {
                    $song = $item->song;
                    if (! $song) {
                        return null;
                    }

                    return [
                        'song' => MbSongTransformer::transform($song),
                        'progress_seconds' => (int) ($item->duration_listened ?? 0),
                        'played_at' => $item->updated_at,
                    ];
                })->filter()->values(),
            ]);
        }

        $playItems = PlayHistory::query()
            ->where('user_id', $userId)
            ->with(['song.album', 'song.artistModel'])
            ->latest('played_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $playItems->map(function (PlayHistory $item) {
                $song = $item->song;
                if (! $song) {
                    return null;
                }

                return [
                    'song' => MbSongTransformer::transform($song),
                    'progress_seconds' => (int) ($item->progress_seconds ?? 0),
                    'played_at' => $item->played_at,
                ];
            })->filter()->values(),
        ]);
    }
}
