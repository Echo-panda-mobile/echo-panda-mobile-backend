<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Song;

/**
 * Mobile (MB) API song payload — mirrors web SongController shape without modifying it.
 */
final class MbSongTransformer
{
    /**
     * DB should store seconds; some rows may have milliseconds (e.g. 180000 → 180s).
     */
    public static function normalizeDurationSeconds(?int $duration): int
    {
        if ($duration === null || $duration <= 0) {
            return 0;
        }

        if ($duration > 10_000) {
            return (int) round($duration / 1000);
        }

        return (int) $duration;
    }

    public static function transform(Song $song): array
    {
        $song->loadMissing(['album', 'artistModel']);

        return [
            'id' => $song->id,
            'title' => $song->title,
            'album_id' => $song->album_id,
            'artist_id' => $song->artist_id,
            'artist_user_id' => $song->artistModel?->user_id,
            'artist' => $song->artistModel
                ? [
                    'id' => $song->artistModel->id,
                    'stage_name' => $song->artistModel->name,
                    'user_id' => $song->artistModel->user_id,
                ]
                : null,
            'artist_name' => $song->artist ?: $song->artistModel?->name,
            'duration' => self::normalizeDurationSeconds($song->duration),
            'track_number' => $song->track_number,
            'lyrics' => $song->lyrics,
            'audio_url' => $song->original_key,
            'original_key' => $song->original_key,
            'cover_key' => $song->cover_key,
            'preview_key' => $song->preview_key,
            'processing_status' => $song->processing_status,
            'published_at' => $song->published_at,
            'play_count' => $song->play_count,
            'created_at' => $song->created_at,
            'updated_at' => $song->updated_at,
            'album' => $song->album,
            'is_favorited' => true,
        ];
    }
}
