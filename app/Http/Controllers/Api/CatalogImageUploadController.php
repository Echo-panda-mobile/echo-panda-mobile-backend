<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Tag;
use App\Services\CatalogImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CatalogImageUploadController extends Controller
{
    protected const MAX_IMAGE_BYTES = 5 * 1024 * 1024;

    public function __construct(
        protected CatalogImageService $catalogImages
    ) {}

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
                'content_type' => 'Only image uploads are allowed.',
            ]);
        }
    }

    protected function presignForModel(Request $request, Genre|Tag $model, string $purpose): JsonResponse
    {
        $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1'],
        ]);

        $filename = basename($request->string('filename')->toString());
        $contentType = strtolower(trim($request->string('content_type')->toString()));
        $size = (int) $request->integer('size');

        $this->assertImageUpload($contentType, $size);

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION)) ?: $this->extensionFromContentType($contentType);
        $key = $this->catalogImages->buildImageKey($model, $extension);

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

    public function presignGenre(Request $request, Genre $genre): JsonResponse
    {
        return $this->presignForModel($request, $genre, 'genre_image');
    }

    public function presignTag(Request $request, Tag $tag): JsonResponse
    {
        return $this->presignForModel($request, $tag, 'tag_image');
    }

    protected function storeFileToS3(UploadedFile $file, Genre|Tag $model): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $key = $this->catalogImages->buildImageKey($model, $ext);

        Storage::disk('s3')->put($key, fopen($file->getRealPath(), 'r'));

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');

        return [
            'key' => $key,
            'url' => $disk->url($key),
        ];
    }

    public function mediaGenre(Request $request, Genre $genre): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $stored = $this->storeFileToS3($request->file('file'), $genre);

        return response()->json([
            'message' => 'File uploaded successfully.',
            'purpose' => 'genre_image',
            'key' => $stored['key'],
            'url' => $stored['url'],
        ]);
    }

    public function mediaTag(Request $request, Tag $tag): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $stored = $this->storeFileToS3($request->file('file'), $tag);

        return response()->json([
            'message' => 'File uploaded successfully.',
            'purpose' => 'tag_image',
            'key' => $stored['key'],
            'url' => $stored['url'],
        ]);
    }
}
