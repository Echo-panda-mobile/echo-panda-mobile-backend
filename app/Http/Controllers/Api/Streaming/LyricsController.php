<?php

namespace App\Http\Controllers\Api\Streaming;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\JsonResponse;

class LyricsController extends Controller
{
    /**
     * Return synced lyrics payload for a song.
     */
    public function show(Song $song): JsonResponse
    {
        $lyric = $song->lyric;

        if (! $lyric) {
            if (! empty($song->lyrics)) {
                return response()->json([
                    'song_id' => $song->id,
                    'format' => 'plain',
                    'language' => null,
                    'lines' => collect(preg_split('/\r\n|\r|\n/', (string) $song->lyrics) ?: [])
                        ->filter(fn (string $line) => trim($line) !== '')
                        ->values()
                        ->map(fn (string $line) => [
                            'time_ms' => 0,
                            'text' => trim($line),
                        ])
                        ->all(),
                ]);
            }

            return response()->json([
                'song_id' => $song->id,
                'format' => 'lrc',
                'lines' => [],
            ]);
        }

        $lines = $lyric->parsed_json ?: ($lyric->format === 'plain'
            ? collect(preg_split('/\r\n|\r|\n/', (string) $lyric->lrc_content) ?: [])
                ->filter(fn (string $line) => trim($line) !== '')
                ->values()
                ->map(fn (string $line) => [
                    'time_ms' => 0,
                    'text' => trim($line),
                ])
                ->all()
            : $this->parseLrc((string) $lyric->lrc_content));

        return response()->json([
            'song_id' => $song->id,
            'format' => $lyric->format,
            'language' => $lyric->language,
            'lines' => $lines,
        ]);
    }

    /**
     * Parse an LRC string into a timeline array.
     *
     * @return array<int, array{time_ms:int,text:string}>
     */
    protected function parseLrc(string $lrc): array
    {
        if ($lrc === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $lrc) ?: [];
        $parsed = [];

        foreach ($lines as $line) {
            if (! preg_match('/^\[(\d{2}):(\d{2})(?:\.(\d{1,3}))?\](.*)$/', trim($line), $matches)) {
                continue;
            }

            $minutes = (int) $matches[1];
            $seconds = (int) $matches[2];
            $fraction = isset($matches[3]) ? (int) str_pad($matches[3], 3, '0') : 0;

            $timeMs = ($minutes * 60 * 1000) + ($seconds * 1000) + $fraction;

            $parsed[] = [
                'time_ms' => $timeMs,
                'text' => trim($matches[4]),
            ];
        }

        usort($parsed, fn (array $a, array $b) => $a['time_ms'] <=> $b['time_ms']);

        return $parsed;
    }
}
