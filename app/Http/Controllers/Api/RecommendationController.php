<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecommendationService;
use App\Models\GeneratedPlaylist;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(protected RecommendationService $service) {}

    /**
     * Get home sections: Daily Mixes, Discover Weekly, Trending.
     */
    public function home(Request $request)
    {
        $user = $request->user();

        // 1. Get or Generate Daily Mix
        $dailyMix = GeneratedPlaylist::where('user_id', $user->id)
            ->where('type', 'daily_mix')
            ->where('created_at', '>=', now()->startOfDay())
            ->first() ?? $this->service->generateDailyMix($user);

        // 2. Get or Generate Discover Weekly
        $discoverWeekly = GeneratedPlaylist::where('user_id', $user->id)
            ->where('type', 'discover_weekly')
            ->where('created_at', '>=', now()->startOfWeek())
            ->first() ?? $this->service->generateDiscoverWeekly($user);

        // 3. Trending Now (Global)
        $trending = GeneratedPlaylist::where('type', 'trending')
            ->where('created_at', '>=', now()->subHours(6))
            ->latest()
            ->first() ?? $this->service->generateTrendingPlaylist($user);

        return response()->json([
            'daily_mix' => $dailyMix->load('songs'),
            'discover_weekly' => $discoverWeekly->load('songs'),
            'trending' => $trending->load('songs')
        ]);
    }
}
