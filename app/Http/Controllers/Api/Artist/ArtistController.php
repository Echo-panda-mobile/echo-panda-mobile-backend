<?php

namespace App\Http\Controllers\Api\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    /**
     * Get all artists (public endpoint).
     */
    public function index(): JsonResponse
    {
        $artists = Artist::query()
            ->where('is_active', '=', 1)
            ->orderBy('id', 'desc')
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
        if (!$artist->image_url) {
            return response()->json(['message' => 'Artist image not available'], 404);
        }

        $signedUrl = Storage::disk('s3')->temporaryUrl($artist->image_url, now()->addMinutes(60));

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
        $imageUrl = $request->string('image_url')->toString() ?: null;

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
}
