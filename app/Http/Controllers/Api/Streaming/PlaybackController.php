<?php

namespace App\Http\Controllers\Api\Streaming;

use App\Http\Controllers\Controller;
use App\Models\PlayHistory;
use App\Models\Song;
use App\Services\Streaming\PlaybackTrackingService;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaybackController extends Controller
{
    /**
     * Track playback progress checkpoints.
     */
    public function progress(Request $request, PlaybackTrackingService $tracking, UserPreferenceService $preferenceService): JsonResponse
    {
        $data = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'progress_seconds' => 'required|integer|min:0',
            'duration_seconds' => 'required|integer|min:1',
            'source' => 'nullable|in:web,android',
        ]);

        $history = $tracking->trackProgress(
            (int) $request->user()->id,
            (int) $data['song_id'],
            (int) $data['progress_seconds'],
            (int) $data['duration_seconds'],
            $data['source'] ?? null
        );

        if ((int) $data['progress_seconds'] <= 15) {
            $song = Song::query()->with(['genre', 'artistModel', 'tag'])->find((int) $data['song_id']);
            if ($song) {
                $preferenceService->applyQuickSkip((int) $request->user()->id, $song);
            }
        }

        return response()->json([
            'message' => 'Playback progress tracked successfully.',
            'data' => $history,
        ]);
    }

    /**
     * Mark playback as completed.
     */
    public function complete(Request $request, PlaybackTrackingService $tracking, UserPreferenceService $preferenceService): JsonResponse
    {
        $data = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'duration_seconds' => 'required|integer|min:1',
            'source' => 'nullable|in:web,android',
        ]);

        $history = $tracking->complete(
            (int) $request->user()->id,
            (int) $data['song_id'],
            (int) $data['duration_seconds'],
            $data['source'] ?? null
        );

        $song = Song::query()->with(['genre', 'artistModel', 'tag'])->find((int) $data['song_id']);
        if ($song) {
            $preferenceService->applyListenActivity(
                (int) $request->user()->id,
                $song,
                (int) $data['duration_seconds'],
                true
            );
        }

        return response()->json([
            'message' => 'Playback completion tracked successfully.',
            'data' => $history,
        ]);
    }

    /**
     * Return the user's recently played songs.
     */
    public function recentlyPlayed(Request $request): JsonResponse
    {
        $items = PlayHistory::query()
            ->where('user_id', $request->user()->id)
            ->with(['song.album', 'song.artistModel'])
            ->latest('played_at')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $items,
        ]);
    }
}
