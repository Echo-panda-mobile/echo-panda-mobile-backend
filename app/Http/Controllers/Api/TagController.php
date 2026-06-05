<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class TagController extends Controller
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

    protected function serializeTag(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'image_url' => $tag->image_url,
        ];
    }

    public function index(): JsonResponse
    {
        $tags = Tag::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Tag $tag) => $this->serializeTag($tag));

        return response()->json([
            'data' => $tags,
        ]);
    }

    /**
     * Get signed image URL for a tag (public endpoint).
     */
    public function imageUrl(Tag $tag): JsonResponse
    {
        $imageKey = $this->keyFromUrlOrKey($tag->getAttributes()['image_url'] ?? null);

        if (! $imageKey) {
            return response()->json(['message' => 'Tag image not available'], 404);
        }

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');
        $signedUrl = $disk->temporaryUrl($imageKey, now()->addMinutes(60));

        return response()->json([
            'tag_id' => $tag->id,
            'signed_url' => $signedUrl,
            'expires_in_seconds' => 3600,
        ]);
    }
}
