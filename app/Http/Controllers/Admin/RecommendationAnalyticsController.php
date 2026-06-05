<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationEvent;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationAnalyticsController extends Controller
{
    public function index(): Response
    {
        $totalServed = RecommendationEvent::query()->where('event_type', 'recommendation_shown')->count();
        $totalClicked = RecommendationEvent::query()->where('event_type', 'recommendation_clicked')->count();
        $totalPlayed = RecommendationEvent::query()->where('event_type', 'recommendation_played')->count();

        $clickRate = $totalServed > 0 ? round(($totalClicked / $totalServed) * 100, 2) : 0;
        $playRate = $totalServed > 0 ? round(($totalPlayed / $totalServed) * 100, 2) : 0;

        $topSongs = RecommendationEvent::query()
            ->with('song')
            ->where('event_type', 'recommendation_played')
            ->select('song_id', DB::raw('COUNT(*) as plays'))
            ->groupBy('song_id')
            ->orderByDesc('plays')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'song_id' => $row->song_id,
                'title' => $row->song?->title,
                'plays' => (int) $row->plays,
            ]);

        $topReasons = RecommendationEvent::query()
            ->whereNotNull('recommendation_reason')
            ->where('recommendation_reason', '!=', '')
            ->select('recommendation_reason', DB::raw('COUNT(*) as total'))
            ->groupBy('recommendation_reason')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'reason' => $row->recommendation_reason,
                'total' => (int) $row->total,
            ]);

        $daily = RecommendationEvent::query()
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw("SUM(CASE WHEN event_type = 'recommendation_shown' THEN 1 ELSE 0 END) as shown")
            ->selectRaw("SUM(CASE WHEN event_type = 'recommendation_clicked' THEN 1 ELSE 0 END) as clicked")
            ->selectRaw("SUM(CASE WHEN event_type = 'recommendation_played' THEN 1 ELSE 0 END) as played")
            ->groupBy('day')
            ->orderBy('day')
            ->limit(30)
            ->get();

        return Inertia::render('Admin/Analytics/Recommendations', [
            'metrics' => [
                'total_recommendations_served' => $totalServed,
                'recommendation_click_rate' => $clickRate,
                'recommendation_play_rate' => $playRate,
                'top_recommended_songs' => $topSongs,
                'most_successful_reasons' => $topReasons,
                'daily' => $daily,
            ],
        ]);
    }
}
