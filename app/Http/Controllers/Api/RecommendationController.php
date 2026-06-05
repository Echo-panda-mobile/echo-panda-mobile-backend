<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecommendationEvent;
use App\Models\Song;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index(Request $request, RecommendationService $recommendationService): JsonResponse
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
            'track_shown' => 'nullable|boolean',
        ]);

        $limit = (int) ($validated['limit'] ?? 20);
        $result = $recommendationService->recommendForUser($request->user(), $limit);

        $trackShown = (bool) ($validated['track_shown'] ?? true);
        if ($trackShown) {
            $this->trackShownEvents((int) $request->user()->id, $result['data'] ?? []);
        }

        return response()->json($result);
    }

    public function similar(Request $request, Song $song, RecommendationService $recommendationService): JsonResponse
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $limit = (int) ($validated['limit'] ?? 10);

        return response()->json($recommendationService->recommendSimilarSongs($song, $limit));
    }

    public function coldStart(Request $request, RecommendationService $recommendationService): JsonResponse
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = (int) ($validated['limit'] ?? 20);

        return response()->json($recommendationService->coldStartRecommendations($limit));
    }

    public function trackEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'song_id' => 'required|integer|exists:songs,id',
            'event_type' => 'required|in:recommendation_shown,recommendation_clicked,recommendation_played,recommendation_skipped',
            'recommendation_score' => 'nullable|numeric|min:0',
            'recommendation_reason' => 'nullable|string|max:255',
        ]);

        $event = RecommendationEvent::query()->create([
            'user_id' => (int) $request->user()->id,
            'song_id' => (int) $validated['song_id'],
            'event_type' => $validated['event_type'],
            'recommendation_score' => (float) ($validated['recommendation_score'] ?? 0),
            'recommendation_reason' => $validated['recommendation_reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Recommendation event tracked',
            'data' => $event,
        ], 201);
    }

    protected function trackShownEvents(int $userId, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $payload = [];
        $now = now();
        foreach ($rows as $row) {
            $songId = (int) ($row['id'] ?? $row['song']['id'] ?? 0);
            if ($songId <= 0) {
                continue;
            }

            $payload[] = [
                'user_id' => $userId,
                'song_id' => $songId,
                'event_type' => 'recommendation_shown',
                'recommendation_score' => (float) ($row['recommendation_score'] ?? 0),
                'recommendation_reason' => $row['recommendation_reason'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($payload)) {
            RecommendationEvent::query()->insert($payload);
        }
    }
}
