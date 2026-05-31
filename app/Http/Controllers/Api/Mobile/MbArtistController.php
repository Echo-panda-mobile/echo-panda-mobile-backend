<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ListenHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Mobile-only artist discovery (MB prefix). Does not modify web-facing /artists routes.
 */
class MbArtistController extends Controller
{
    private function transformArtist(Artist $artist): array
    {
        $playCount = (int) ($artist->total_play_count ?? $artist->songs_sum_play_count ?? 0);

        return [
            'id' => (string) $artist->id,
            'name' => $artist->name,
            'slug' => $artist->slug,
            'image_url' => $artist->image_url,
            'bio' => $artist->bio,
            'play_count' => $playCount,
            'songs_count' => (int) ($artist->songs_count ?? 0),
            'monthly_listeners' => $this->formatListenerLabel($playCount),
        ];
    }

    private function formatListenerLabel(int $playCount): string
    {
        if ($playCount >= 1_000_000) {
            return round($playCount / 1_000_000, 1).'M';
        }
        if ($playCount >= 1_000) {
            return round($playCount / 1_000, 1).'K';
        }

        return (string) max($playCount, 0);
    }

    /**
     * Ranked by total song streams + platform listen-history plays (global popularity).
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->get('limit', 20), 1), 50);

        $listenScores = ListenHistory::query()
            ->join('songs', 'songs.id', '=', 'user_listen_history.song_id')
            ->where('songs.is_active', true)
            ->whereNotNull('songs.artist_id')
            ->groupBy('songs.artist_id')
            ->select('songs.artist_id', DB::raw('SUM(user_listen_history.play_count) as listen_score'))
            ->pluck('listen_score', 'songs.artist_id');

        $artists = Artist::query()
            ->where('is_active', true)
            ->whereHas('songs', fn ($query) => $query->where('is_active', true))
            ->withCount(['songs as songs_count' => fn ($query) => $query->where('is_active', true)])
            ->withSum(['songs as songs_sum_play_count' => fn ($query) => $query->where('is_active', true)], 'play_count')
            ->get()
            ->map(function (Artist $artist) use ($listenScores) {
                $streamScore = (int) ($artist->songs_sum_play_count ?? 0);
                $listenScore = (int) ($listenScores[$artist->id] ?? 0);
                $artist->setAttribute('total_play_count', $streamScore + $listenScore);

                return $artist;
            })
            ->sort(function (Artist $a, Artist $b) {
                $scoreCmp = ((int) ($b->total_play_count ?? 0)) <=> ((int) ($a->total_play_count ?? 0));
                if ($scoreCmp !== 0) {
                    return $scoreCmp;
                }

                return strcmp($a->name, $b->name);
            })
            ->take($limit)
            ->values();

        return response()->json([
            'data' => $artists->map(fn (Artist $artist) => $this->transformArtist($artist))->all(),
        ]);
    }

    /**
     * Random active artists for mobile home discovery (not ranked by popularity).
     */
    public function random(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->get('limit', 8), 1), 30);

        $artists = Artist::query()
            ->where('is_active', true)
            ->whereHas('songs', fn ($query) => $query->where('is_active', true))
            ->withCount(['songs as songs_count' => fn ($query) => $query->where('is_active', true)])
            ->withSum(['songs as songs_sum_play_count' => fn ($query) => $query->where('is_active', true)], 'play_count')
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $artists->map(fn (Artist $artist) => $this->transformArtist($artist))->all(),
        ]);
    }
}
