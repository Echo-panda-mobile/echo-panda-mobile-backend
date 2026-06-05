<?php

namespace App\Services;

use App\Models\Song;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Cache;

class UserPreferenceService
{
    public const PLAY_DELTA = 1;
    public const HALF_LISTEN_BONUS = 2;
    public const COMPLETE_BONUS = 2;
    public const FAVORITE_DELTA = 10;
    public const PLAYLIST_ADD_DELTA = 8;
    public const QUICK_SKIP_DELTA = -5;

    public function applyListenActivity(int $userId, Song $song, int $durationListened = 0, bool $completed = false): void
    {
        $song->loadMissing(['genre', 'artistModel', 'tag']);

        $delta = self::PLAY_DELTA;
        $durationSeconds = max((int) $song->duration, 0);

        if ($durationSeconds > 0 && $durationListened >= (int) floor($durationSeconds * 0.5)) {
            $delta += self::HALF_LISTEN_BONUS;
        }

        if ($completed) {
            $delta += self::COMPLETE_BONUS;
        }

        $this->applySongSignal($userId, $song, $delta);

        if (! $completed && $durationListened > 0 && $durationListened <= 15) {
            $this->applySongSignal($userId, $song, self::QUICK_SKIP_DELTA);
        }
    }

    public function applyFavorite(int $userId, Song $song): void
    {
        $song->loadMissing(['genre', 'artistModel', 'tag']);
        $this->applySongSignal($userId, $song, self::FAVORITE_DELTA);
    }

    public function applyPlaylistAdd(int $userId, Song $song): void
    {
        $song->loadMissing(['genre', 'artistModel', 'tag']);
        $this->applySongSignal($userId, $song, self::PLAYLIST_ADD_DELTA);
    }

    public function applyQuickSkip(int $userId, Song $song): void
    {
        $song->loadMissing(['genre', 'artistModel', 'tag']);
        $this->applySongSignal($userId, $song, self::QUICK_SKIP_DELTA);
    }

    protected function applySongSignal(int $userId, Song $song, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        $dimensions = [
            'genre' => $song->genre?->name,
            'mood' => $song->mood,
            'artist' => $song->artistModel?->name ?: $song->artist,
            'tag' => $song->tag?->name,
        ];

        foreach ($dimensions as $type => $value) {
            $value = trim((string) ($value ?? ''));
            if ($value === '') {
                continue;
            }

            $this->adjustPreference($userId, $type, $value, $delta);
        }
    }

    protected function adjustPreference(int $userId, string $type, string $value, int $delta): void
    {
        $preference = UserPreference::query()->firstOrNew([
            'user_id' => $userId,
            'preference_type' => $type,
            'preference_value' => $value,
        ]);

        if (! $preference->exists) {
            $preference->genre = $type === 'genre' ? $value : sprintf('%s:%s', $type, $value);
            $preference->preference_score = max(0, $delta);
            $preference->save();
            $this->bumpRecommendationVersion($userId);

            return;
        }

        $preference->preference_score = max(0, (int) $preference->preference_score + $delta);

        if (empty($preference->genre)) {
            $preference->genre = $type === 'genre' ? $value : sprintf('%s:%s', $type, $value);
        }

        $preference->save();
        $this->bumpRecommendationVersion($userId);
    }

    protected function bumpRecommendationVersion(int $userId): void
    {
        $key = sprintf('recommendations:user:%d:version', $userId);
        $current = (int) Cache::get($key, 1);
        Cache::forever($key, max(2, $current + 1));
    }
}
