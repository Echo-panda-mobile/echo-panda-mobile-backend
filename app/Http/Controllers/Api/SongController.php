<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Jobs\ProcessUploadedSong;
use App\Models\Song;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SongController extends Controller
{
    use AuthorizesRequests;

    protected function transformSong(Song $song): array
    {
        return [
            'id' => $song->id,
            'title' => $song->title,
            'album_id' => $song->album_id,
            'artist_id' => $song->artist_id,
            'artist_user_id' => $song->artistModel?->user_id,
            'artist' => $song->artistModel
                ? [
                    'id' => $song->artistModel->id,
                    'stage_name' => $song->artistModel->name,
                    'user_id' => $song->artistModel->user_id,
                ]
                : null,
            'artist_name' => $song->artist,
            'duration' => $song->duration,
            'track_number' => $song->track_number,
            'lyrics' => $song->lyrics,
            'category_id' => $song->category_id,
            'tag_id' => $song->tag_id,
            'genre' => $song->category_id ? Genre::find($song->category_id) : null,
            'mood' => $song->mood,
            'song_type' => $song->song_type,
            'bpm' => $song->bpm,
            'is_explicit' => (bool) $song->is_explicit,
            'featured_artists' => $song->featured_artists,
            'audio_url' => $song->audio_url,
            'original_key' => $song->original_key,
            'variant_key_320' => $song->variant_key_320,
            'variant_key_128' => $song->variant_key_128,
            'cover_key' => $song->cover_key,
            'preview_key' => $song->preview_key,
            'processing_status' => $song->processing_status,
            'published_at' => $song->published_at,
            'play_count' => $song->play_count,
            'is_active' => (bool) ($song->is_active ?? true),
            'created_at' => $song->created_at,
            'updated_at' => $song->updated_at,
            'album' => $song->album,
        ];
    }

    /**
     * Backfill artist_id for legacy songs owned via album or display name.
     */
    protected function repairSongArtistLink(?User $user, Song $song): void
    {
        if (! $user || $user->isAdmin()) {
            return;
        }

        $artist = $user->artist;
        if (! $artist) {
            return;
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
        }
    }

    protected function resolveGenreId(mixed $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        // Try to find by slug
        $genre = Genre::where('slug', $value)->first();
        if ($genre) {
            return $genre->id;
        }

        // Try to find by name
        $genre = Genre::where('name', 'like', $value)->first();
        if ($genre) {
            return $genre->id;
        }

        return null;
    }

    /**
     * Display a listing of songs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Song::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
                    ->orWhere('lyrics', 'like', "%{$search}%");
            });
        }

        // Filter by album
        if ($request->has('album_id')) {
            $query->where('album_id', $request->get('album_id'));
        }

        // Filter by genre (category) or tag
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('tag_id')) {
            $query->where('tag_id', $request->get('tag_id'));
        }

        $query->where('is_active', true);

        // Sort by track number or latest
        $sortBy = $request->get('sort_by', 'track_number');
        if ($sortBy === 'latest') {
            $query->latest();
        } else {
            $query->orderBy('track_number', 'asc');
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $songs = $query->with(['album', 'artistModel'])->paginate($perPage);

        $songs->setCollection(
            $songs->getCollection()->map(fn (Song $song) => $this->transformSong($song))
        );

        return response()->json($songs);
    }

    /**
     * Store a newly created song.
     */
    public function store(StoreSongRequest $request): JsonResponse
    {
        $this->authorize('create', Song::class);

        $userArtist = $request->user()->artist;
        if (! $userArtist) {
            return response()->json([
                'message' => 'Artist profile not found for this account.',
            ], 403);
        }

        $payload = $request->validated();
        $payload['category_id'] = $this->resolveGenreId($payload['category_id'] ?? null);
        $payload['artist_id'] = $userArtist->id;
        $payload['artist'] = $userArtist->name;
        $payload['processing_status'] = ! empty($payload['original_key']) ? 'uploaded' : ($payload['processing_status'] ?? 'draft');

        $song = Song::create($payload);
        $song->load(['album', 'artistModel']);

        if (! empty($song->original_key)) {
            ProcessUploadedSong::dispatch($song->id)->afterCommit();
        }

        return response()->json([
            'message' => 'Song created successfully',
            'data' => $this->transformSong($song),
        ], 201);
    }

    /**
     * Display the specified song.
     */
    public function show(Song $song): JsonResponse
    {
        $song->load(['album', 'artistModel']);

        return response()->json($this->transformSong($song));
    }

    /**
     * Update the specified song.
     */
    public function update(UpdateSongRequest $request, Song $song): JsonResponse
    {
        $this->repairSongArtistLink($request->user(), $song);
        $this->authorize('update', $song);

        $validated = $request->validated();

        if (array_key_exists('category_id', $validated)) {
            $validated['category_id'] = $this->resolveGenreId($validated['category_id']);
        }

        $song->update($validated);
        $song->load(['album', 'artistModel']);

        if (! empty($validated['original_key'] ?? null)) {
            $song->processing_status = 'uploaded';
            $song->processing_error = null;
            $song->save();
            ProcessUploadedSong::dispatch($song->id)->afterCommit();
        }

        return response()->json([
            'message' => 'Song updated successfully',
            'data' => $this->transformSong($song),
        ]);
    }

    /**
     * Remove the specified song.
     */
    public function destroy(Song $song): JsonResponse
    {
        $this->repairSongArtistLink(request()->user(), $song);
        $this->authorize('delete', $song);

        $song->delete();

        return response()->json([
            'message' => 'Song deleted successfully',
        ]);
    }

    /**
     * Get songs by album.
     */
    public function getByAlbum(Request $request, int $albumId): JsonResponse
    {
        $query = Song::where('album_id', $albumId)->orderBy('track_number', 'asc');

        $perPage = $request->get('per_page', 20);
        $songs = $query->with(['album', 'artistModel'])->paginate($perPage);

        $songs->setCollection(
            $songs->getCollection()->map(fn (Song $song) => $this->transformSong($song))
        );

        return response()->json($songs);
    }
}
