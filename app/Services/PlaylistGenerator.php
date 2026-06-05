<?php

namespace App\Services;

use App\Models\Song;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PlaylistGenerator
{
    /**
     * Generate a list of ranked songs based on criteria.
     */
    public function generate(array $criteria, User $user, int $limit = 30): Collection
    {
        $genreNames = (array) ($criteria['genres'] ?? ($criteria['genre'] ?? []));
        $tagNames = (array) ($criteria['tags'] ?? []);
        $mood = $criteria['mood'] ?? null;
        $artistName = $criteria['artist'] ?? ($criteria['similar_artist'] ?? null);
        $excludeIds = $criteria['exclude_ids'] ?? [];

        // Base query
        $query = Song::query()
            ->with(['genre', 'artists'])
            ->where('is_active', true);

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        // Fetch candidates (limit to 500 for performance before ranking)
        $songs = $query->limit(500)->get();

        // Calculate Scores
        $rankedSongs = $songs->map(function ($song) use ($genreNames, $tagNames, $mood, $artistName, $user) {
            $score = 0;

            // 1. Genre Match (Weight: 30)
            if (!empty($genreNames)) {
                foreach ($genreNames as $g) {
                    if ($song->genre && stripos($song->genre->name, $g) !== false) {
                        $score += 30;
                        break;
                    }
                }
            }

            // 2. Tag Match (Weight: 25)
            $targetTags = array_merge($tagNames, $mood ? [$mood] : []);
            if (!empty($targetTags)) {
                $songTags = $song->mood ?? ""; // Fallback if tags table not used directly
                $matchCount = 0;
                foreach ($targetTags as $tag) {
                    if (stripos($songTags, $tag) !== false) $matchCount++;
                }
                $score += (count($targetTags) > 0) ? ($matchCount / count($targetTags)) * 25 : 0;
            }

            // 3. Artist Match (Weight: 15)
            if ($artistName) {
                $artistMatch = false;
                if (stripos($song->artist, $artistName) !== false) $artistMatch = true;
                foreach ($song->artists as $a) {
                    if (stripos($a->name, $artistName) !== false) $artistMatch = true;
                }
                if ($artistMatch) $score += 15;
            }

            // 4. User History Match (Weight: 15)
            // If user liked it or played it many times
            $isLiked = DB::table('favorites')
                ->where('user_id', $user->id)
                ->where('favoritable_id', $song->id)
                ->where('favoritable_type', Song::class)
                ->exists();
            if ($isLiked) $score += 10;

            $playHistory = DB::table('user_listen_history')
                ->where('user_id', $user->id)
                ->where('song_id', $song->id)
                ->first();
            if ($playHistory) $score += min(5, $playHistory->play_count);

            // 5. Popularity Score (Weight: 10)
            // Normalize play_count (max 10 points)
            $score += min(10, ($song->play_count / 5000) * 10);

            // 6. Trending Score (Weight: 5)
            // Recent interactions in last 24h
            $recentPlays = DB::table('user_interactions')
                ->where('song_id', $song->id)
                ->where('action', 'play')
                ->where('created_at', '>=', now()->subDay())
                ->count();
            $score += min(5, ($recentPlays / 100) * 5);

            $song->recommendation_score = $score;
            return $song;
        });

        return $rankedSongs->sortByDesc('recommendation_score')->take($limit)->values();
    }
}
