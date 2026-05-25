<?php

namespace App\Http\Controllers\Api\Streaming;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Services\Streaming\StreamTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StreamTicketController extends Controller
{
    protected function resolveSignedUrl(?string $source, array $temporaryUrlOptions = []): ?string
    {
        if (! $source) {
            return null;
        }

        if (preg_match('#^https?://#i', $source)) {
            return $source;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        return $disk->temporaryUrl(
            ltrim($source, '/'),
            now()->addMinutes(60),
            $temporaryUrlOptions
        );
    }

    /**
     * Create a short-lived stream ticket for an authenticated user or guest.
     */
    public function show(Request $request, Song $song, StreamTokenService $tokens): JsonResponse
    {
        abort_unless($song->is_active, 404, 'Song is not available.');

        $data = $request->validate([
            'quality' => 'nullable|in:128,320',
        ]);

        $quality = $data['quality'] ?? ($song->default_quality ?: '320');
        $userId = (int) ($request->user()?->id ?? 0);
        $streamUrl = $tokens->makeSignedStreamUrl($song->id, $quality, $userId);

        return response()->json([
            'song_id' => $song->id,
            'quality' => $quality,
            'expires_in_seconds' => 300,
            'stream_url' => $streamUrl,
        ]);
    }

    /**
     * Return a short-lived S3 GET URL for direct browser streaming (Range requests supported by S3).
     */
    public function signedUrl(Request $request, Song $song): JsonResponse
    {
        abort_unless($song->is_active, 404, 'Song is not available.');

        $key = $song->original_key;
        abort_if(! $key, 404, 'Requested audio is not available.');

        $url = $this->resolveSignedUrl($key, ['ResponseContentType' => 'audio/mpeg']);
        abort_if(! $url, 404, 'Requested audio is not available.');

        return response()->json([
            'song_id' => $song->id,
            'quality' => $song->default_quality ?: 'original',
            'expires_in_seconds' => 3600,
            'signed_url' => $url,
        ]);
    }

    /**
     * Return a short-lived S3 GET URL for the song cover image.
     */
    public function coverUrl(Request $request, Song $song): JsonResponse
    {
        abort_unless($song->is_active, 404, 'Song is not available.');

        $coverSource = $song->cover_key
            ?: $song->song_cover_url
            ?: $song->album?->cover_key
            ?: $song->album?->cover_url;

        abort_if(! $coverSource, 404, 'Song cover is not available.');

        $coverKey = preg_match('#^https?://#i', (string) $coverSource)
            ? ltrim((string) parse_url((string) $coverSource, PHP_URL_PATH), '/')
            : ltrim((string) $coverSource, '/');

        abort_if($coverKey === '', 404, 'Song cover is not available.');

        $url = $this->resolveSignedUrl($coverSource ?? $coverKey);
        abort_if(! $url, 404, 'Song cover is not available.');

        return response()->json([
            'song_id' => $song->id,
            'signed_url' => $url,
            'expires_in_seconds' => 3600,
        ]);
    }
}
