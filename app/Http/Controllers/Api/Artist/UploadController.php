<?php

namespace App\Http\Controllers\Api\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadController extends Controller
{
    protected function artistForRequest(Request $request): ?Artist
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return Artist::query()->where('user_id', $user->id)->first();
    }

    protected function storeFileToS3(UploadedFile $file, string $folder, Artist $artist): array
    {
        $ext = $file->getClientOriginalExtension();
        $uuid = (string) Str::uuid();
        $artistSlug = Str::slug($artist->name ?: 'artist-'.$artist->id);
        $key = trim($folder, '/')."/{$artistSlug}/{$uuid}.{$ext}";

        Storage::disk('s3')->put($key, fopen($file->getRealPath(), 'r'));

        return [
            'key' => $key,
            'url' => $this->publicS3Url($key),
        ];
    }

    protected function publicS3Url(string $key): string
    {
        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');

        return $disk->url($key);
    }

    protected function mediaSpecForPurpose(string $purpose): array
    {
        return match ($purpose) {
            'album_cover', 'song_cover', 'artist_image', 'playlist_cover' => [
                'folder' => 'images',
                'max_bytes' => 5 * 1024 * 1024,
            ],
            'song_audio' => [
                'folder' => 'audio/original',
                'max_bytes' => 50 * 1024 * 1024,
            ],
            'song_lyrics' => [
                'folder' => 'lyrics/synced',
                'max_bytes' => 1024 * 1024,
            ],
            default => throw ValidationException::withMessages([
                'purpose' => 'Unsupported upload purpose.',
            ]),
        };
    }

    protected function extensionFromContentType(string $contentType): string
    {
        return match ($contentType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'audio/mpeg', 'audio/mp3' => 'mp3',
            'audio/wav', 'audio/x-wav' => 'wav',
            'audio/flac' => 'flac',
            'audio/aac' => 'aac',
            'text/plain' => 'txt',
            'application/json' => 'json',
            default => 'bin',
        };
    }

    protected function normalizeUploadHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $key => $value) {
            if (strtolower((string) $key) === 'host') {
                continue;
            }

            $normalized[$key] = is_array($value) ? implode(', ', $value) : $value;
        }

        return $normalized;
    }

    public function presignMedia(Request $request): JsonResponse
    {
        $request->validate([
            'purpose' => ['required', 'string', 'in:album_cover,song_cover,song_audio,artist_image,song_lyrics,playlist_cover'],
            'filename' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1'],
        ]);

        $purpose = $request->string('purpose')->toString();

        // Playlist covers can be uploaded by any authenticated user
        if ($purpose !== 'playlist_cover') {
            $artist = $this->artistForRequest($request);
            if (! $artist) {
                return response()->json(['message' => 'Artist profile not found for this user.'], 403);
            }
            $ownerSlug = Str::slug($artist->name ?: 'artist-'.$artist->id);
        } else {
            $user = $request->user();
            $ownerSlug = 'user-'.$user->id;
        }

        $filename = basename($request->string('filename')->toString());
        $contentType = strtolower(trim($request->string('content_type')->toString()));
        $size = (int) $request->integer('size');
        $spec = $this->mediaSpecForPurpose($purpose);

        if ($size > $spec['max_bytes']) {
            throw ValidationException::withMessages([
                'size' => 'The selected file exceeds the allowed size for this upload.',
            ]);
        }

        $uuid = (string) Str::uuid();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION)) ?: $this->extensionFromContentType($contentType);

        $key = match ($purpose) {
            'album_cover' => "images/album-covers/{$ownerSlug}/{$uuid}.{$extension}",
            'song_cover' => "images/song-covers/{$ownerSlug}/{$uuid}.{$extension}",
            'artist_image' => "images/artist-images/{$ownerSlug}/{$uuid}.{$extension}",
            'playlist_cover' => "images/playlist-covers/{$ownerSlug}/{$uuid}.{$extension}",
            'song_audio' => "audio/original/{$ownerSlug}/{$uuid}.{$extension}",
            'song_lyrics' => "lyrics/synced/{$ownerSlug}/{$uuid}.{$extension}",
        };

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');
        $upload = $disk->temporaryUploadUrl(
            $key,
            now()->addMinutes(15),
            ['ContentType' => $contentType ?: 'application/octet-stream']
        );

        return response()->json([
            'message' => 'Upload URL generated successfully.',
            'purpose' => $purpose,
            'key' => $key,
            'url' => $disk->url($key),
            'upload_url' => $upload['url'],
            'headers' => $this->normalizeUploadHeaders($upload['headers']),
        ]);
    }

    protected function keyFromUrlOrKey(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            return ltrim($value, '/');
        }

        $path = parse_url($value, PHP_URL_PATH);

        return $path ? ltrim(rawurldecode($path), '/') : null;
    }

    public function media(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
            'purpose' => ['required', 'string', 'in:album_cover,song_cover,song_audio,artist_image,song_lyrics,playlist_cover'],
        ]);

        $purpose = $request->string('purpose')->toString();

        if ($purpose !== 'playlist_cover') {
            $artist = $this->artistForRequest($request);
            if (! $artist) {
                return response()->json(['message' => 'Artist profile not found for this user.'], 403);
            }
            $ownerSlug = Str::slug($artist->name ?: 'artist-'.$artist->id);
            $owner = $artist;
        } else {
            $owner = $request->user();
            $ownerSlug = 'user-'.$owner->id;
        }

        $file = $request->file('file');

        if (in_array($purpose, ['album_cover', 'song_cover', 'artist_image', 'playlist_cover'], true)) {
            $request->validate([
                'file' => 'image|max:2048',
            ]);
        } else {
            $request->validate([
                'file' => 'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/flac,audio/aac,text/plain,application/octet-stream|max:51200',
            ]);
        }

        $folder = match ($purpose) {
            'album_cover' => 'images/album-covers',
            'song_cover' => 'images/song-covers',
            'artist_image' => 'images/artist-images',
            'playlist_cover' => 'images/playlist-covers',
            'song_audio' => 'audio/original',
            'song_lyrics' => 'lyrics/synced',
        };

        // We need a small helper to handle folder/slug logic consistently
        $ext = $file->getClientOriginalExtension();
        $uuid = (string) Str::uuid();
        $key = trim($folder, '/')."/{$ownerSlug}/{$uuid}.{$ext}";

        Storage::disk('s3')->put($key, fopen($file->getRealPath(), 'r'));

        return response()->json([
            'message' => 'File uploaded successfully.',
            'purpose' => $purpose,
            'key' => $key,
            'url' => $this->publicS3Url($key),
        ]);
    }

    public function deleteMedia(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required_without:url|string',
            'url' => 'required_without:key|string',
        ]);

        $key = $this->keyFromUrlOrKey($request->input('key') ?: $request->input('url'));
        if (! $key) {
            return response()->json(['message' => 'Invalid media key.'], 422);
        }

        Storage::disk('s3')->delete($key);

        return response()->json([
            'message' => 'File deleted successfully.',
            'key' => $key,
        ]);
    }

}
