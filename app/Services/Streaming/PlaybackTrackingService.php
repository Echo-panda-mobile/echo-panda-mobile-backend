<?php

namespace App\Services\Streaming;

use App\Models\PlayHistory;
use App\Models\Song;
use Illuminate\Support\Facades\DB;

class PlaybackTrackingService
{
    /**
     * Track a progress checkpoint and decide if it should count as a play.
     */
    public function trackProgress(int $userId, int $songId, int $progressSeconds, int $durationSeconds, ?string $source = null): PlayHistory
    {
        $history = PlayHistory::create([
            'user_id' => $userId,
            'song_id' => $songId,
            'played_at' => now(),
            'progress_seconds' => $progressSeconds,
            'completed' => false,
            'source' => $source,
        ]);

        if ($this->shouldIncrementPlayCount($progressSeconds, $durationSeconds)) {
            $this->incrementSongPlayCount($songId);
        }

        return $history;
    }

    /**
     * Mark a song as completed and always increment the play counter.
     */
    public function complete(int $userId, int $songId, int $durationSeconds, ?string $source = null): PlayHistory
    {
        $history = PlayHistory::create([
            'user_id' => $userId,
            'song_id' => $songId,
            'played_at' => now(),
            'progress_seconds' => $durationSeconds,
            'completed' => true,
            'source' => $source,
        ]);

        $this->incrementSongPlayCount($songId);

        return $history;
    }

    public function shouldIncrementPlayCount(int $progressSeconds, int $durationSeconds): bool
    {
        if ($durationSeconds <= 0) {
            return false;
        }

        return $progressSeconds >= 30 || ($progressSeconds / $durationSeconds) >= 0.5;
    }

    protected function incrementSongPlayCount(int $songId): void
    {
        DB::table('songs')
            ->where('id', $songId)
            ->update([
                'play_count' => DB::raw('play_count + 1'),
                'updated_at' => now(),
            ]);
    }
}
