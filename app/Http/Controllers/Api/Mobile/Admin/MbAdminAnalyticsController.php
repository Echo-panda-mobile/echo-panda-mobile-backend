<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class MbAdminAnalyticsController extends Controller
{
    public function index(): JsonResponse
    {
        $points = User::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->date)->toDateString(),
                'count' => (int) $row->count,
            ]);

        return response()->json([
            'data' => $points,
            'summary' => [
                'total_users' => User::count(),
                'total_artists' => Artist::count(),
                'total_albums' => Album::count(),
                'total_songs' => Song::count(),
            ],
        ]);
    }
}
