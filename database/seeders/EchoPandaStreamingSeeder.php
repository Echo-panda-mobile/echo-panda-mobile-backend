<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Lyric;
use App\Models\Song;
use Illuminate\Database\Seeder;

class EchoPandaStreamingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $artist = Artist::updateOrCreate(
            ['slug' => 'echo-panda'],
            [
                'name' => 'Echo Panda',
                'bio' => 'Echo Panda is a synth-pop and lo-fi project built for cinematic late-night listening.',
                'image_url' => 'https://placehold.co/800x800/png?text=Echo+Panda',
                'is_active' => true,
            ]
        );

        $album = Album::updateOrCreate(
            ['slug' => 'midnight-neon'],
            [
                'artist_id' => $artist->id,
                'title' => 'Midnight Neon',
                'artist' => $artist->name,
                'release_date' => '2026-05-21',
                'description' => 'A warm, atmospheric release designed to demonstrate secure audio streaming.',
                'cover_key' => 'https://placehold.co/1200x1200/png?text=Midnight+Neon',
            ]
        );

        $song = Song::updateOrCreate(
            ['slug' => 'neon-river'],
            [
                'album_id' => $album->id,
                'artist_id' => $artist->id,
                'title' => 'Neon River',
                'artist' => $artist->name,
                'duration' => 248,
                'track_number' => 1,
                'lyrics' => null,
                'audio_url' => null,
                'lyrics_url' => null,
                'file_size_bytes' => 9345021,
                'mime_type' => 'audio/mpeg',
                'variant_key_128' => 'audio/128/midnight-neon/neon-river.mp3',
                'variant_key_320' => 'audio/320/midnight-neon/neon-river.mp3',
                'default_quality' => '320',
                'is_active' => true,
                'play_count' => 0,
                'published_at' => now(),
            ]
        );

        $lrc = <<<'LRC'
[00:00.00]Echo Panda
[00:08.00]City lights are waking up
[00:16.00]Silver streets and humming signs
[00:24.00]Rolling through the afterglow
[00:32.00]Let the midnight carry us
[00:40.00]Closer to the neon river
[00:48.00]Every heartbeat finds a rhythm
[00:56.00]Every shadow learns to shine
[01:04.00]Hold the moment, never lose it
[01:12.00]We are drifting, soft and bright
[01:20.00]Meet me where the future glows
[01:28.00]And the night becomes a light
LRC;

        Lyric::updateOrCreate(
            ['song_id' => $song->id],
            [
                'format' => 'lrc',
                'lrc_content' => $lrc,
                'parsed_json' => $this->parseLrc($lrc),
                'language' => 'en',
            ]
        );
    }

    /**
     * Convert LRC content into an array of synced lyric lines.
     *
     * @return array<int, array{time_ms:int,text:string}>
     */
    protected function parseLrc(string $lrc): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($lrc)) ?: [];
        $parsed = [];

        foreach ($lines as $line) {
            if (! preg_match('/^\[(\d{2}):(\d{2})\.(\d{2})\](.*)$/', $line, $matches)) {
                continue;
            }

            $minutes = (int) $matches[1];
            $seconds = (int) $matches[2];
            $centiseconds = (int) $matches[3];

            $parsed[] = [
                'time_ms' => ($minutes * 60 * 1000) + ($seconds * 1000) + ($centiseconds * 10),
                'text' => trim($matches[4]),
            ];
        }

        return $parsed;
    }
}
