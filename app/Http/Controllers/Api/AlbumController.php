<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Models\Album;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AlbumController extends Controller
{
    use AuthorizesRequests;

    protected function resolveCoverUrl(?string $coverSource): ?string
    {
        if (! $coverSource) {
            return null;
        }

        if (preg_match('#^https?://#i', $coverSource)) {
            return $coverSource;
        }

        /** @var mixed $disk */
        $disk = Storage::disk('s3');

        if (! method_exists($disk, 'temporaryUrl')) {
            return null;
        }

        return $disk->temporaryUrl(ltrim($coverSource, '/'), now()->addMinutes(60));
    }

    protected function transformAlbum(Album $album): array
    {
        $coverUrl = $this->resolveCoverUrl($album->cover_key);

        return [
            'id' => $album->id,
            'title' => $album->title,
            'artist_id' => $album->artist_id,
            'artist_user_id' => $album->artistModel?->user_id,
            'artist' => $album->artistModel
                ? [
                    'id' => $album->artistModel->id,
                    'stage_name' => $album->artistModel->name,
                    'user_id' => $album->artistModel->user_id,
                ]
                : null,
            'artist_name' => $album->artist,
            'release_date' => $album->release_date,
            'description' => $album->description,
            'release_status' => $album->release_status,
            'scheduled_at' => $album->scheduled_at,
            'cover_key' => $album->cover_key,
            'cover_url' => $coverUrl,
            'created_at' => $album->created_at,
            'updated_at' => $album->updated_at,
            'songs' => $album->songs,
        ];
    }

    /**
     * Display a listing of albums.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Album::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort by latest or oldest
        $sortBy = $request->get('sort_by', 'latest');
        if ($sortBy === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $albums = $query->with(['artistModel'])->paginate($perPage);

        $albums->setCollection(
            $albums->getCollection()->map(fn (Album $album) => $this->transformAlbum($album))
        );

        return response()->json($albums);
    }

    /**
     * Store a newly created album.
     */
    public function store(StoreAlbumRequest $request): JsonResponse
    {
        $this->authorize('create', Album::class);

        $userArtist = $request->user()->artist;
        if (! $userArtist) {
            return response()->json([
                'message' => 'Artist profile not found for this account.',
            ], 403);
        }

        $payload = $request->validated();
        $payload['artist_id'] = $userArtist->id;
        $payload['artist'] = $userArtist->name;
        $payload['release_status'] = $payload['release_status'] ?? ($payload['release_date'] ? 'published' : 'draft');

        if (! empty($payload['cover_key'] ?? null)) {
            $payload['cover_key'] = ltrim((string) $payload['cover_key'], '/');
        }

        $album = Album::create($payload);
        $album->load(['songs', 'artistModel']);

        return response()->json([
            'message' => 'Album created successfully',
            'data' => $this->transformAlbum($album),
        ], 201);
    }

    /**
     * Display the specified album with its songs.
     */
    public function show(Album $album): JsonResponse
    {
        $album->load(['songs', 'artistModel']);

        return response()->json($this->transformAlbum($album));
    }

    /**
     * Return a temporary S3 URL for the album cover.
     */
    public function coverUrl(Album $album): JsonResponse
    {
        $coverSource = $album->cover_key;
        abort_if(! $coverSource, 404, 'Album cover is not available.');

        $coverKey = ltrim((string) $coverSource, '/');
        abort_if($coverKey === '', 404, 'Album cover is not available.');

        $url = $this->resolveCoverUrl($coverKey);
        abort_if(! $url, 404, 'Album cover is not available.');

        return response()->json([
            'album_id' => $album->id,
            'signed_url' => $url,
            'expires_in_seconds' => 3600,
        ]);
    }

    /**
     * Update the specified album.
     */
    public function update(UpdateAlbumRequest $request, Album $album): JsonResponse
    {
        $this->authorize('update', $album);

        $payload = $request->validated();

        if (! empty($payload['cover_key'] ?? null)) {
            $payload['cover_key'] = ltrim((string) $payload['cover_key'], '/');
        }

        $album->update($payload);
        $album->load(['songs', 'artistModel']);

        return response()->json([
            'message' => 'Album updated successfully',
            'data' => $this->transformAlbum($album),
        ]);
    }

    /**
     * Remove the specified album.
     */
    public function destroy(Album $album): JsonResponse
    {
        $this->authorize('delete', $album);

        $album->delete();

        return response()->json([
            'message' => 'Album deleted successfully',
        ]);
    }
}
