<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Song;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Mobile-only artist song edit APIs (MB prefix). Does not modify web-facing song routes.
 */
class MbArtistSongController extends Controller
{
    use AuthorizesRequests;

    public function formOptions(): JsonResponse
    {
        $genres = Genre::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Genre $genre) => [
                'id' => (string) $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
            ])
            ->values();

        $tags = Tag::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Tag $tag) => [
                'id' => (string) $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])
            ->values();

        return response()->json([
            'data' => [
                'genres' => $genres,
                'tags' => $tags,
            ],
        ]);
    }

    public function show(Song $song): JsonResponse
    {
        $this->ensureArtistOwnsSong($song);

        $song->load(['album', 'artistModel']);

        return response()->json([
            'data' => $this->transformSong($song),
        ]);
    }

    public function update(Request $request, Song $song): JsonResponse
    {
        $this->ensureArtistOwnsSong($song);

        $validated = $request->validate([
            'album_id' => ['required', 'exists:albums,id'],
            'title' => ['required', 'string', 'max:255'],
            'duration' => ['required', 'integer', 'min:1'],
            'track_number' => ['required', 'integer', 'min:1'],
            'lyrics' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:genres,id'],
            'tag_id' => ['nullable', 'integer', 'exists:tags,id'],
            'cover_key' => ['nullable', 'string', 'max:1024'],
        ]);

        if (array_key_exists('cover_key', $validated) && ! empty($validated['cover_key'])) {
            $this->assertSongCoverKey($song, $validated['cover_key']);
        } elseif (array_key_exists('cover_key', $validated) && $validated['cover_key'] === null) {
            unset($validated['cover_key']);
        }

        $song->update($validated);
        $song->refresh()->load(['album', 'artistModel']);

        return response()->json([
            'message' => 'Song updated successfully',
            'data' => $this->transformSong($song),
        ]);
    }

    /**
     * Mobile catalog may include legacy songs where artist_id was not set consistently.
     * Resolve ownership via artist_id, album.artist_id, or artist display name, then repair links.
     */
    protected function ensureArtistOwnsSong(Song $song): void
    {
        /** @var User|null $user */
        $user = request()->user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        if ($user->isAdmin()) {
            return;
        }

        $artist = $user->artist;
        if (! $artist) {
            abort(403, 'Artist profile not found for this account.');
        }

        if ((int) $song->artist_id === (int) $artist->id) {
            return;
        }

        $song->loadMissing(['album', 'artistModel']);

        if ($song->album && (int) $song->album->artist_id === (int) $artist->id) {
            $song->forceFill([
                'artist_id' => $artist->id,
                'artist' => $artist->name,
            ])->save();

            return;
        }

        $songName = trim((string) ($song->artist ?? $song->artistModel?->name ?? ''));
        $artistName = trim((string) $artist->name);
        if ($songName !== '' && $artistName !== '' && strcasecmp($songName, $artistName) === 0) {
            $song->forceFill([
                'artist_id' => $artist->id,
                'artist' => $artist->name,
            ])->save();

            return;
        }

        abort(403, 'This action is unauthorized.');
    }

    protected function transformSong(Song $song): array
    {
        return [
            'id' => $song->id,
            'title' => $song->title,
            'album_id' => $song->album_id,
            'duration' => $song->duration,
            'track_number' => $song->track_number,
            'lyrics' => $song->lyrics,
            'category_id' => $song->category_id,
            'tag_id' => $song->tag_id,
            'cover_key' => $song->getRawOriginal('cover_key'),
            'cover_url' => $song->cover_url,
        ];
    }

    protected function assertSongCoverKey(Song $song, string $coverKey): void
    {
        $storedKey = $song->getRawOriginal('cover_key');
        if ($storedKey && $coverKey === $storedKey) {
            return;
        }

        $artist = $song->artistModel;
        $artistSlug = Str::slug($artist?->name ?: 'artist-'.($artist?->id ?? $song->artist_id));
        $expectedPrefix = "images/song-covers/{$artistSlug}/";

        if (! str_starts_with($coverKey, $expectedPrefix)) {
            abort(422, 'The provided cover image does not belong to this artist.');
        }

        if (! Storage::disk('s3')->exists($coverKey)) {
            abort(422, 'The uploaded cover image could not be found in storage.');
        }
    }
}
