<?php

namespace App\Services;

use App\Models\Song;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlaylistGenerator
{
    /**
     * Generate a list of ranked songs based on criteria.
     */
    public function generate(array $criteria, User $user, int $limit = 30)
    {
        $genreName = $criteria['genre'] ?? null;
        $tagsNames = $criteria['tags'] ?? [];
        $mood = $criteria['mood'] ?? null;
        $similarArtist = $criteria['similar_artist'] ?? null;
        $language = $criteria['language'] ?? null;

        // Base query with relationships
        $query = Song::query()
            ->with(['genre', 'artistModel'])
            ->where('is_active', true);

        // Optimization: Filter broad candidates first if any specific major filter exists
        if ($genreName) {
            $query->whereHas('genre', function($q) use ($genreName) {
                $q->where('name', 'ILIKE', "%{$genreName}%");
            });
        }

        $songs = $query->get();

        // Map and Score
        $rankedSongs = $songs->map(function ($song) use ($genreName, $tagsNames, $mood, $similarArtist, $user) {
            $score = 0;

            // 1. Genre Match (Weight: 30)
            if ($genreName && $song->genre && stripos($song->genre->name, $genreName) !== false) {
                $score += 30;
            }

            // 2. Tag/Mood Match (Weight: 30)
            // Assuming mood is treated as a tag in the system
            $targetTags = array_merge($tagsNames, $mood ? [$mood] : []);
            $songTags = $song->mood; // Assuming mood field in song model holds string or comma separated tags

            $tagMatchCount = 0;
            foreach ($targetTags as $tag) {
                if (stripos($songTags, $tag) !== false) {
                    $tagMatchCount++;
                }
            }
            if (count($targetTags) > 0) {
                $score += ($tagMatchCount / count($targetTags)) * 30;
            }

            // 3. Artist Match (Weight: 20)
            if ($similarArtist && stripos($song->artist, $similarArtist) !== false) {
                $score += 20;
            }

            // 4. User History/Preference Match (Weight: 10)
            // Simplified: If user has favorited this song or songs by same artist
            $isFavorite = DB::table('favorites')
                ->where('user_id', $user->id)
                ->where('favoritable_id', $song->id)
                ->where('favoritable_type', Song::class)
                ->exists();
            if ($isFavorite) $score += 10;

            // 5. Trending Score (Weight: 10)
            // Map play_count to a score of 0-10
            $trendingScore = min(10, ($song->play_count / 1000));
            $score += $trendingScore;

            $song->ai_score = $score;
            return $song;
        });

        // Filter out zero scores if we have enough results
        $filtered = $rankedSongs->sortByDesc('ai_score')->take($limit);

        return $filtered->values();
    }
}
