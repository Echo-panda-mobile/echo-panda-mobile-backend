<?php

namespace App\Services\Streaming;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class S3RangeStreamService
{
    /**
     * Stream an S3 object as full or partial content depending on Range header.
     */
    public function streamObject(string $bucket, string $key, ?string $range, string $mime, int $totalSize): StreamedResponse
    {
        /** @var mixed $disk */
        $disk = Storage::disk('s3');
        $signedUrl = $disk->temporaryUrl($key, now()->addMinutes(5));
        $requestHeaders = $range ? ['Range' => $range] : [];

        $client = new Client([
            'http_errors' => false,
        ]);

        $upstream = $client->request('GET', $signedUrl, [
            'headers' => $requestHeaders,
            'stream' => true,
        ]);

        $status = $upstream->getStatusCode();
        $upstreamBody = $upstream->getBody();
        $upstreamContentRange = $upstream->getHeaderLine('Content-Range');
        $upstreamContentLength = $upstream->getHeaderLine('Content-Length');

        $headers = [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($upstreamContentLength !== '') {
            $headers['Content-Length'] = $upstreamContentLength;
        }

        if ($upstreamContentRange !== '') {
            $headers['Content-Range'] = $upstreamContentRange;
        } elseif ($status === 206) {
            [$start, $end] = $this->parseRange($range, $totalSize);
            $headers['Content-Range'] = sprintf('bytes %d-%d/%d', $start, $end, $totalSize);
        }

        return response()->stream(function () use ($upstreamBody) {
            while (! $upstreamBody->eof()) {
                echo $upstreamBody->read(8192);
                flush();
            }
        }, $status, $headers);
    }

    /**
     * Parse HTTP range request into start/end bytes.
     *
     * @return array{0:int,1:int}
     */
    public function parseRange(?string $range, int $totalSize): array
    {
        if (! $range || ! str_starts_with($range, 'bytes=')) {
            return [0, max(0, $totalSize - 1)];
        }

        $value = str_replace('bytes=', '', $range);
        [$rawStart, $rawEnd] = array_pad(explode('-', $value, 2), 2, '');

        $start = $rawStart === '' ? 0 : max(0, (int) $rawStart);
        $end = $rawEnd === '' ? ($totalSize - 1) : min($totalSize - 1, (int) $rawEnd);

        if ($start > $end) {
            return [0, max(0, $totalSize - 1)];
        }

        return [$start, $end];
    }
}
