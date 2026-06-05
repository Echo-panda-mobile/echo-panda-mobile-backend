<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Share;
use App\Models\Song;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    /**
     * Track a share event.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_type' => 'required|string|in:song,album,artist,playlist',
            'target_id' => 'required|integer',
            'platform' => 'required|string|in:instagram,facebook,messenger,telegram,whatsapp,copy_link',
        ]);

        $share = Share::create([
            'user_id' => $request->user()?->id,
            'target_type' => $validated['target_type'],
            'target_id' => $validated['target_id'],
            'platform' => $validated['platform'],
        ]);

        return response()->json([
            'message' => 'Share tracked successfully',
            'data' => $share
        ], 201);
    }

    /**
     * Get share analytics.
     */
    public function analytics(): JsonResponse
    {
        $mostSharedSongs = Share::where('target_type', 'song')
            ->select('target_id', DB::raw('count(*) as share_count'))
            ->groupBy('target_id')
            ->orderByDesc('share_count')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'song' => Song::find($s->target_id)?->title,
                'count' => $s->share_count
            ]);

        $platformDistribution = Share::select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'most_shared_songs' => $mostSharedSongs,
            'platform_distribution' => $platformDistribution,
        ]);
    }
}
