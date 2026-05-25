<?php

namespace App\Services\Streaming;

use Illuminate\Support\Facades\URL;

class StreamTokenService
{
    /**
     * Generate a short-lived signed URL for a song stream endpoint.
     */
    public function makeSignedStreamUrl(int $songId, string $quality, int $userId): string
    {
        return URL::temporarySignedRoute(
            'api.streaming.audio',
            now()->addMinutes(5),
            [
                'song' => $songId,
                'quality' => $quality,
                'uid' => $userId,
            ]
        );
    }
}
