<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Genre;
use App\Models\ListenHistory;
use App\Models\Song;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecommendationService
{
    private const RECOMMENDATION_TTL_SECONDS = 900;

    public function recommendForUser(User $user, int $limit = 20): array
    {
        $limit = max(1, min($limit, 50));

        $cacheKey = sprintf(
            'recommendations:user:%d:v:%d:limit:%d',
            (int) $user->id,
            $this->getUserRecommendationVersion((int) $user->id),
            $limit
        );

        return Cache::remember($cacheKey, self::RECOMMENDATION_TTL_SECONDS, function () use ($user, $limit) {
            return $this->buildRecommendationsForUser($user, $limit);
        });
    }

    public function recommendSimilarSongs(Song $song, int $limit = 10): array
    {
        $limit = max(1, min($limit, 20));

        $cacheKey = sprintf('recommendations:similar:song:%d:limit:%d', (int) $song->id, $limit);

        return Cache::remember($cacheKey, self::RECOMMENDATION_TTL_SECONDS, function () use ($song, $limit) {
            $song->loadMissing(['artistModel', 'genre', 'tag', 'album']);

            $candidates = Song::query()
                ->with(['album', 'artistModel', 'genre', 'tag'])
                ->where('is_active', true)
                ->where('id', '!=', $song->id)
                ->where(function ($q) use ($song) {
                    if ($song->artist_id) {
                        $q->orWhere('artist_id', $song->artist_id);
                    }

                    if ($song->category_id) {
                        $q->orWhere('category_id', $song->category_id);
                    }

                    if (! empty($song->mood)) {
                        $q->orWhere('mood', $song->mood);
                    }

                    if ($song->tag_id) {
                        $q->orWhere('tag_id', $song->tag_id);
                    }
                })
                ->limit($limit * 6)
                ->get();

            $scored = $candidates->map(function (Song $candidate) use ($song) {
                $sameArtist = (int) ($song->artist_id && $candidate->artist_id === $song->artist_id);
                $sameGenre = (int) ($song->category_id && $candidate->category_id === $song->category_id);
                $sameMood = (int) (! empty($song->mood) && $candidate->mood === $song->mood);
                $sameTag = (int) ($song->tag_id && $candidate->tag_id === $song->tag_id);

                $score = ($sameArtist * 45)
                    + ($sameGenre * 25)
                    + ($sameMood * 15)
                    + ($sameTag * 15)
                    + min(10, (int) floor(((int) $candidate->play_count) / 50));

                $parts = [];
                if ($sameArtist) {
                    $parts[] = 'same artist';
                }
                if ($sameGenre) {
                    $parts[] = 'same genre';
                }
                if ($sameMood) {
                    $parts[] = 'similar mood';
                }
                if ($sameTag) {
                    $parts[] = 'same tag';
                }

                $reason = empty($parts)
                    ? 'Because this song is currently popular'
                    : 'Because it matches by '.implode(', ', array_slice($parts, 0, 2));

                return [
                    'id' => (int) $candidate->id,
                    'title' => $candidate->title,
                    'recommendation_score' => $score,
                    'recommendation_reason' => $reason,
                    'song' => $this->serializeSong($candidate),
                ];
            })->sortByDesc('recommendation_score')->values()->take($limit)->all();

            return [
                'data' => $scored,
                'meta' => [
                    'mode' => 'similar',
                    'source_song_id' => (int) $song->id,
                ],
            ];
        });
    }

    public function coldStartRecommendations(int $limit = 20): array
    {
        $limit = max(1, min($limit, 50));
        $cacheKey = sprintf('recommendations:cold-start:limit:%d', $limit);

        return Cache::remember($cacheKey, self::RECOMMENDATION_TTL_SECONDS, function () use ($limit) {
            return $this->buildColdStartRecommendations($limit);
        });
    }

    protected function buildRecommendationsForUser(User $user, int $limit): array
    {

        $preferences = UserPreference::query()
            ->where('user_id', $user->id)
            ->where('preference_score', '>', 0)
            ->orderByDesc('preference_score')
            ->get();

        if ($preferences->isEmpty()) {
            return $this->buildColdStartRecommendations($limit);
        }

        $prefMaps = $this->buildPreferenceMaps($preferences);
        $candidates = $this->buildCandidates($prefMaps, $user->id, max(250, $limit * 12));

        if ($candidates->isEmpty()) {
            return $this->buildColdStartRecommendations($limit);
        }

        $favoriteSongIds = Favorite::query()
            ->where('user_id', $user->id)
            ->where('favoritable_type', Song::class)
            ->pluck('favoritable_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $favoriteSet = array_flip($favoriteSongIds);

        $scored = $candidates->map(function (Song $song) use ($prefMaps, $favoriteSet) {
            $artistName = trim((string) ($song->artistModel?->name ?: $song->artist ?: ''));
            $genreName = trim((string) ($song->genre?->name ?: ''));
            $moodName = trim((string) ($song->mood ?: ''));

            $artistScore = $artistName !== '' ? ($prefMaps['artist'][$artistName] ?? 0) : 0;
            $genreScore = $genreName !== '' ? ($prefMaps['genre'][$genreName] ?? 0) : 0;
            $moodScore = $moodName !== '' ? ($prefMaps['mood'][$moodName] ?? 0) : 0;
            $tagName = trim((string) ($song->tag?->name ?: ''));

            $tagScore = $tagName !== '' ? ($prefMaps['tag'][$tagName] ?? 0) : 0;
            $popularityScore = min(100.0, (float) max((int) $song->play_count, 0));

            $score = ($artistScore * 0.35)
                + ($genreScore * 0.25)
                + ($moodScore * 0.20)
                + ($tagScore * 0.10)
                + ($popularityScore * 0.1);

            if (isset($favoriteSet[(int) $song->id])) {
                $score += 8.0;
            }

            return [
                'score' => round($score, 2),
                'artist_score' => $artistScore,
                'genre_score' => $genreScore,
                'mood_score' => $moodScore,
                'tag_score' => $tagScore,
                'popularity_score' => round($popularityScore, 2),
                'song' => $song,
            ];
        })->sortByDesc('score')->values();

        $data = $scored->take($limit)->map(function (array $item) {
            /** @var Song $song */
            $song = $item['song'];

            $reasonText = $this->buildReasonText([
                'artist' => (float) $item['artist_score'],
                'genre' => (float) $item['genre_score'],
                'mood' => (float) $item['mood_score'],
                'tag' => (float) $item['tag_score'],
                'popularity' => (float) $item['popularity_score'],
            ], $song);

            return [
                'id' => (int) $song->id,
                'title' => $song->title,
                'recommendation_score' => $item['score'],
                'recommendation_reason' => $reasonText,
                'reason' => [
                    'artist' => $item['artist_score'],
                    'genre' => $item['genre_score'],
                    'mood' => $item['mood_score'],
                    'tag' => $item['tag_score'],
                    'popularity' => $item['popularity_score'],
                ],
                'song' => $this->serializeSong($song),
            ];
        })->all();

        return [
            'data' => $data,
            'meta' => [
                'mode' => 'personalized',
                'candidate_count' => $candidates->count(),
                'formula' => [
                    'artist' => 0.35,
                    'genre' => 0.25,
                    'mood' => 0.20,
                    'tag' => 0.10,
                    'popularity' => 0.1,
                ],
            ],
        ];
    }

    protected function buildPreferenceMaps(Collection $preferences): array
    {
        $maps = [
            'artist' => [],
            'genre' => [],
            'mood' => [],
            'tag' => [],
        ];

        foreach ($preferences as $pref) {
            $type = $pref->resolved_type;
            $value = trim((string) $pref->resolved_value);

            if ($value === '' || ! array_key_exists($type, $maps)) {
                continue;
            }

            $maps[$type][$value] = max((int) ($maps[$type][$value] ?? 0), (int) $pref->preference_score);
        }

        return $maps;
    }

    protected function buildCandidates(array $prefMaps, int $userId, int $limit): Collection
    {
        $genreValues = array_slice(array_keys($prefMaps['genre']), 0, 5);
        $moodValues = array_slice(array_keys($prefMaps['mood']), 0, 5);
        $artistValues = array_slice(array_keys($prefMaps['artist']), 0, 5);
        $tagValues = array_slice(array_keys($prefMaps['tag']), 0, 5);

        $genreIds = [];
        if (! empty($genreValues)) {
            $slugs = array_map(fn (string $name) => Str::slug($name), $genreValues);
            $genreIds = Genre::query()
                ->whereIn('name', $genreValues)
                ->orWhereIn('slug', $slugs)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $recentSongIds = ListenHistory::query()
            ->where('user_id', $userId)
            ->latest('updated_at')
            ->limit(20)
            ->pluck('song_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $query = Song::query()
            ->with(['album', 'artistModel', 'genre', 'tag'])
            ->where('is_active', true)
            ->where(function ($q) use ($genreIds, $moodValues, $artistValues, $tagValues) {
                if (! empty($genreIds)) {
                    $q->orWhereIn('category_id', $genreIds);
                }

                if (! empty($moodValues)) {
                    $q->orWhereIn('mood', $moodValues);
                }

                if (! empty($artistValues)) {
                    $q->orWhereHas('artistModel', fn ($sq) => $sq->whereIn('name', $artistValues))
                        ->orWhereIn('artist', $artistValues);
                }

                if (! empty($tagValues)) {
                    $q->orWhereHas('tag', fn ($sq) => $sq->whereIn('name', $tagValues));
                }
            })
            ->limit($limit);

        if (! empty($recentSongIds)) {
            $query->whereNotIn('id', $recentSongIds);
        }

        return $query->get();
    }

    protected function buildColdStartRecommendations(int $limit): array
    {
        $rows = ListenHistory::query()
            ->select('song_id', DB::raw('SUM(play_count) as play_count'))
            ->groupBy('song_id')
            ->orderByDesc('play_count')
            ->limit((int) ceil($limit * 0.6))
            ->get();

        $songIds = $rows->pluck('song_id')->all();

        $songs = Song::query()
            ->with(['album', 'artistModel', 'genre', 'tag'])
            ->whereIn('id', $songIds)
            ->get()
            ->keyBy('id');

        $trending = $rows->map(function ($row) use ($songs) {
            $song = $songs->get((int) $row->song_id);
            if (! $song) {
                return null;
            }

            return [
                'id' => (int) $song->id,
                'title' => $song->title,
                'recommendation_score' => (float) $row->play_count,
                'recommendation_reason' => 'Trending now based on total plays',
                'reason' => [
                    'artist' => 0,
                    'genre' => 0,
                    'mood' => 0,
                    'tag' => 0,
                    'popularity' => (float) $row->play_count,
                ],
                'song' => $this->serializeSong($song),
            ];
        })->filter()->values()->all();

        $remaining = max(0, $limit - count($trending));

        $recentlyAdded = [];
        if ($remaining > 0) {
            $recentSongs = Song::query()
                ->with(['album', 'artistModel', 'genre', 'tag'])
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->limit((int) ceil($remaining * 0.7))
                ->get();

            $recentlyAdded = $recentSongs->map(function (Song $song) {
                return [
                    'id' => (int) $song->id,
                    'title' => $song->title,
                    'recommendation_score' => 55.0,
                    'recommendation_reason' => 'Recently added to Echo Panda',
                    'reason' => [
                        'artist' => 0,
                        'genre' => 0,
                        'mood' => 0,
                        'tag' => 0,
                        'popularity' => 55,
                    ],
                    'song' => $this->serializeSong($song),
                ];
            })->all();
        }

        $editorPicks = [];
        $left = max(0, $limit - count($trending) - count($recentlyAdded));
        if ($left > 0) {
            $pickSongs = Song::query()
                ->with(['album', 'artistModel', 'genre', 'tag'])
                ->where('is_active', true)
                ->orderByDesc('play_count')
                ->limit($left)
                ->get();

            $editorPicks = $pickSongs->map(function (Song $song) {
                return [
                    'id' => (int) $song->id,
                    'title' => $song->title,
                    'recommendation_score' => 50.0,
                    'recommendation_reason' => 'Editor pick for new listeners',
                    'reason' => [
                        'artist' => 0,
                        'genre' => 0,
                        'mood' => 0,
                        'tag' => 0,
                        'popularity' => 50,
                    ],
                    'song' => $this->serializeSong($song),
                ];
            })->all();
        }

        $data = collect(array_merge($trending, $recentlyAdded, $editorPicks))
            ->unique('id')
            ->take($limit)
            ->values()
            ->all();

        return [
            'data' => $data,
            'meta' => [
                'mode' => 'cold_start',
                'candidate_count' => count($data),
            ],
        ];
    }

    protected function buildReasonText(array $scores, Song $song): string
    {
        arsort($scores);
        $top = array_key_first($scores);

        if ($top === 'artist' && $scores['artist'] > 0 && ($song->artistModel?->name || $song->artist)) {
            $artist = $song->artistModel?->name ?: $song->artist;

            return sprintf('Because you frequently listen to %s', $artist);
        }

        if ($top === 'genre' && $scores['genre'] > 0 && $song->genre?->name) {
            return sprintf('Because you often enjoy %s music', $song->genre->name);
        }

        if ($top === 'mood' && $scores['mood'] > 0 && ! empty($song->mood)) {
            return sprintf('Because your listening mood is often %s', $song->mood);
        }

        if ($top === 'tag' && $scores['tag'] > 0 && $song->tag?->name) {
            return sprintf('Because you engage with %s tagged songs', $song->tag->name);
        }

        return 'Because this song is popular among Echo Panda listeners';
    }

    protected function getUserRecommendationVersion(int $userId): int
    {
        return max(1, (int) Cache::get(sprintf('recommendations:user:%d:version', $userId), 1));
    }

    protected function serializeSong(Song $song): array
    {
        return [
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artistModel?->name ?: $song->artist,
            'artist_id' => $song->artist_id,
            'album' => $song->album,
            'genre' => $song->genre?->name,
            'mood' => $song->mood,
            'tag' => $song->tag?->name,
            'duration' => $song->duration,
            'play_count' => $song->play_count,
            'audio_url' => $song->original_key ?: $song->variant_key_320 ?: $song->variant_key_128,
            'cover_key' => $song->cover_key,
        ];
    }
}
