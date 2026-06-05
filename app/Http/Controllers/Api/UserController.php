<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
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

    /**
     * Get signed image URL for a user (public endpoint).
     */
    public function imageUrl(User $user): JsonResponse
    {
        $imageKey = $this->keyFromUrlOrKey($user->getAttributes()['image_url'] ?? null);

        if (! $imageKey) {
            return response()->json(['message' => 'User image not available'], 404);
        }

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');
        $signedUrl = $disk->temporaryUrl($imageKey, now()->addMinutes(60));

        return response()->json([
            'user_id' => $user->id,
            'signed_url' => $signedUrl,
            'expires_in_seconds' => 3600,
        ]);
    }
}
