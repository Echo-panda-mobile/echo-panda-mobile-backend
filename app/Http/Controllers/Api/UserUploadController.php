<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserUploadController extends Controller
{
    protected const MAX_IMAGE_BYTES = 5 * 1024 * 1024;

    protected function userSlug(User $user): string
    {
        return Str::slug($user->name ?: 'user-'.$user->id);
    }

    protected function buildUserImageKey(User $user, string $extension): string
    {
        $uuid = (string) Str::uuid();
        $slug = $this->userSlug($user);

        return "images/user-images/{$slug}/{$uuid}.{$extension}";
    }

    protected function extensionFromContentType(string $contentType): string
    {
        return match ($contentType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
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

    protected function assertImageUpload(string $contentType, int $size): void
    {
        if ($size > self::MAX_IMAGE_BYTES) {
            throw ValidationException::withMessages([
                'size' => 'The selected file exceeds the allowed size for this upload.',
            ]);
        }

        if (! str_starts_with(strtolower($contentType), 'image/')) {
            throw ValidationException::withMessages([
                'content_type' => 'Only image uploads are allowed for user profile photos.',
            ]);
        }
    }

    public function presign(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $filename = basename($request->string('filename')->toString());
        $contentType = strtolower(trim($request->string('content_type')->toString()));
        $size = (int) $request->integer('size');

        $this->assertImageUpload($contentType, $size);

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION)) ?: $this->extensionFromContentType($contentType);
        $key = $this->buildUserImageKey($user, $extension);

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');
        $upload = $disk->temporaryUploadUrl(
            $key,
            now()->addMinutes(15),
            ['ContentType' => $contentType ?: 'application/octet-stream']
        );

        return response()->json([
            'message' => 'Upload URL generated successfully.',
            'purpose' => 'user_image',
            'key' => $key,
            'url' => $disk->url($key),
            'upload_url' => $upload['url'],
            'headers' => $this->normalizeUploadHeaders($upload['headers']),
        ]);
    }

    protected function storeFileToS3(UploadedFile $file, User $user): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $key = $this->buildUserImageKey($user, $ext);

        Storage::disk('s3')->put($key, fopen($file->getRealPath(), 'r'));

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');

        return [
            'key' => $key,
            'url' => $disk->url($key),
        ];
    }

    public function media(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $stored = $this->storeFileToS3($request->file('file'), $user);

        return response()->json([
            'message' => 'File uploaded successfully.',
            'purpose' => 'user_image',
            'key' => $stored['key'],
            'url' => $stored['url'],
        ]);
    }
}
