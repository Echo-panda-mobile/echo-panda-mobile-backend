<?php

namespace App\Http\Controllers\Api\Streaming;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\StreamLog;
use App\Services\Streaming\S3RangeStreamService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AudioStreamController extends Controller
{
    /**
     * Stream audio bytes securely from S3 using signed URL and range requests.
     */
    public function stream(Request $request, Song $song, string $quality, S3RangeStreamService $streamer): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 401, 'Invalid stream signature.');
        abort_unless($song->is_active, 404, 'Song is not available.');

        $signedUserId = (int) $request->query('uid');
        $loggedUserId = $signedUserId > 0 ? $signedUserId : null;

        $key = $quality === '128' ? $song->variant_key_128 : $song->variant_key_320;
        abort_if(! $key, 404, 'Requested quality is not available.');

        $bucket = (string) config('filesystems.disks.s3.bucket');
        $mime = $song->mime_type ?: 'audio/mpeg';
        $totalSize = max(1, (int) $song->file_size_bytes);
        $range = $request->header('Range');
        [$start, $end] = $streamer->parseRange($range, $totalSize);

        StreamLog::create([
            'user_id' => $loggedUserId,
            'song_id' => $song->id,
            'quality' => $quality,
            'range_start' => $start,
            'range_end' => $end,
            'bytes_sent' => max(0, ($end - $start + 1)),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'status_code' => $range ? 206 : 200,
            'started_at' => now(),
            'ended_at' => now(),
        ]);

        return $streamer->streamObject(
            $bucket,
            $key,
            $range,
            $mime,
            $totalSize
        );
    }
}
