<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MbAdminAlbumController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Album::query()->withCount('songs')->with('artistModel');

        if ($request->filled('genre_id')) {
            $genreId = (int) $request->get('genre_id');
            $query->whereHas('songs', fn ($q) => $q->where('category_id', $genreId));
        }

        // Tags are not linked to albums in the schema yet; keep filter param for mobile compatibility.
        if ($request->filled('tag_id')) {
            $query->whereRaw('0 = 1');
        }

        $albums = $query->latest()->get()->map(fn (Album $album) => $this->serializeAlbum($album));

        return response()->json(['data' => $albums]);
    }

    public function update(Request $request, Album $album): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'release_status' => ['sometimes', 'string', 'max:50'],
            'release_date' => ['sometimes', 'nullable', 'date'],
            'cover_key' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        if (array_key_exists('cover_key', $validated) && $validated['cover_key'] !== null) {
            $validated['cover_key'] = ltrim((string) $validated['cover_key'], '/');
        }

        $album->update($validated);
        $album->loadCount('songs')->load('artistModel');

        return response()->json($this->serializeAlbum($album));
    }

    public function approve(Album $album): JsonResponse
    {
        $album->update(['release_status' => 'published']);

        return response()->json(['message' => 'Album approved and published.']);
    }

    public function hide(Album $album): JsonResponse
    {
        $album->update(['release_status' => 'rejected']);

        return response()->json(['message' => 'Album hidden from the platform.']);
    }

    public function report(Request $request, Album $album): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:5000'],
        ]);

        Report::create([
            'reportable_type' => Album::class,
            'reportable_id' => $album->id,
            'user_id' => $request->user()?->id,
            'reason' => $validated['reason'] ?? 'Flagged by admin via mobile app',
            'details' => $validated['details'] ?? null,
            'status' => 'open',
        ]);

        return response()->json(['message' => 'Album reported for moderation review.']);
    }

    protected function serializeAlbum(Album $album): array
    {
        $coverSource = $album->cover_key;
        $coverUrl = null;

        if ($coverSource && preg_match('#^https?://#i', $coverSource)) {
            $coverUrl = $coverSource;
        } elseif ($coverSource) {
            /** @var mixed $disk */
            $disk = Storage::disk('s3');
            if (method_exists($disk, 'temporaryUrl')) {
                $coverUrl = $disk->temporaryUrl(ltrim($coverSource, '/'), now()->addMinutes(60));
            }
        }

        return [
            'id' => $album->id,
            'title' => $album->title,
            'artist_name' => $album->artist,
            'artist' => $album->artistModel ? [
                'id' => $album->artistModel->id,
                'stage_name' => $album->artistModel->name,
                'user_id' => $album->artistModel->user_id,
            ] : null,
            'release_date' => $album->release_date,
            'description' => $album->description,
            'release_status' => $album->release_status,
            'cover_key' => $album->cover_key,
            'cover_url' => $coverUrl,
            'songs_count' => $album->songs_count ?? $album->songs()->count(),
        ];
    }
}
