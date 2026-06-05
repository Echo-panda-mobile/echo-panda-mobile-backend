<?php

namespace App\Http\Controllers\Api\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    use AuthorizesRequests;
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
     * Get all artists (public endpoint).
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->get('limit', 50), 1), 200);
        $search = trim((string) $request->get('search', ''));

        $query = Artist::query()
            ->where('is_active', '=', 1);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        $artists = $query
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $artists->map(fn (Artist $artist) => [
                'id' => $artist->id,
                'name' => $artist->name,
                'slug' => $artist->slug,
                'image_url' => $artist->image_url,
                'bio' => $artist->bio,
            ])->toArray(),
        ]);
    }

    /**
     * Get signed image URL for an artist (public endpoint).
     */
    public function imageUrl(Artist $artist): JsonResponse
    {
        $imageKey = $this->keyFromUrlOrKey($artist->image_url);

        if (! $imageKey) {
            return response()->json(['message' => 'Artist image not available'], 404);
        }

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $disk = Storage::disk('s3');
        $signedUrl = $disk->temporaryUrl($imageKey, now()->addMinutes(60));

        return response()->json([
            'artist_id' => $artist->id,
            'signed_url' => $signedUrl,
            'expires_in_seconds' => 3600,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->artist) {
            return response()->json(['message' => 'Artist already exists', 'artist' => $user->artist], 422);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:2048',
        ]);

        $name = $request->string('name')->toString();
        $imageUrl = $this->keyFromUrlOrKey($request->string('image_url')->toString() ?: null);

        $slugBase = Str::slug($name ?: ($user->name ?: 'artist')) ?: 'artist';
        $slug = $slugBase;
        $i = 1;
        while (Artist::where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.(++$i);
        }

        $artist = Artist::create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => $slug,
            'image_url' => $imageUrl,
        ]);

        // set user's role to artist
        $user->update(['role' => 'artist']);

        return response()->json([
            'message' => 'Artist created successfully',
            'artist' => $artist,
            'user' => [
                'id' => $user->id,
                'role' => $user->role,
                'artist_id' => $artist->id,
            ],
        ]);
    }

    /**
     * Update the authenticated artist's public profile (bio, image, display name).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $artist = $user->artist;
        if (! $artist) {
            return response()->json(['message' => 'Artist profile not found for this user.'], 404);
        }

        $this->authorize('update', $artist);

        $validated = $request->validate([
            'id' => 'sometimes|integer',
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:5000',
            'image_url' => 'nullable|string|max:2048',
            'cover_image_url' => 'nullable|string|max:2048',
        ]);

        if (isset($validated['id']) && (int) $validated['id'] !== (int) $artist->id) {
            return response()->json(['message' => 'Artist id mismatch.'], 403);
        }

        $updates = [];

        if (! empty($validated['name'])) {
            $updates['name'] = $validated['name'];
        }

        if (array_key_exists('bio', $validated)) {
            $updates['bio'] = $validated['bio'];
        }

        $imageInput = $validated['image_url'] ?? $validated['cover_image_url'] ?? null;
        if (is_string($imageInput) && $imageInput !== '') {
            $imageKey = $this->keyFromUrlOrKey($imageInput);
            if ($imageKey !== null) {
                $this->assertArtistOwnsImageKey($artist, $imageKey);
                $updates['image_url'] = $imageKey;
                $updates['cover_image_url'] = $imageKey;
            }
        }

        if ($updates !== []) {
            $artist->update($updates);
            $artist->refresh();
        }

        return response()->json([
            'message' => 'Artist profile updated successfully',
            'data' => $this->serializeArtistProfile($artist),
        ]);
    }

    protected function serializeArtistProfile(Artist $artist): array
    {
        return [
            'id' => $artist->id,
            'name' => $artist->name,
            'slug' => $artist->slug,
            'image_url' => $artist->getRawOriginal('image_url'),
            'cover_image_url' => $artist->getRawOriginal('cover_image_url'),
            'bio' => $artist->bio,
        ];
    }

    protected function assertArtistOwnsImageKey(Artist $artist, string $imageKey): void
    {
        $storedKey = $artist->getRawOriginal('image_url');
        if ($storedKey && $imageKey === $storedKey) {
            return;
        }

        $artistSlug = Str::slug($artist->name ?: 'artist-'.$artist->id);
        $expectedPrefix = "images/artist-images/{$artistSlug}/";

        if (! str_starts_with($imageKey, $expectedPrefix)) {
            abort(422, 'The provided image does not belong to this artist.');
        }

        if (! Storage::disk('s3')->exists($imageKey)) {
            abort(422, 'The uploaded image could not be found in storage.');
        }
    }
}
