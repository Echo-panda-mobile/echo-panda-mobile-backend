<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class GenreController extends Controller
{
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

    protected function serializeGenre(Genre $genre): array
    {
        return [
            'id' => $genre->id,
            'name' => $genre->name,
            'slug' => $genre->slug,
            'image_url' => $genre->image_url,
            'created_at' => $genre->created_at,
            'updated_at' => $genre->updated_at,
        ];
    }

    /**
     * Display a listing of genres.
     */
    public function index(): JsonResponse
    {
        $genres = Genre::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Genre $genre) => $this->serializeGenre($genre));

        return response()->json([
            'data' => $genres,
        ]);
    }

    /**
     * Get signed image URL for a genre (public endpoint).
     */
    public function imageUrl(Genre $genre): JsonResponse
    {
        $imageKey = $this->keyFromUrlOrKey($genre->getAttributes()['image_url'] ?? null);

        if (! $imageKey) {
            return response()->json(['message' => 'Genre image not available'], 404);
        }

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');
        $signedUrl = $disk->temporaryUrl($imageKey, now()->addMinutes(60));

        return response()->json([
            'genre_id' => $genre->id,
            'signed_url' => $signedUrl,
            'expires_in_seconds' => 3600,
        ]);
    }
}
