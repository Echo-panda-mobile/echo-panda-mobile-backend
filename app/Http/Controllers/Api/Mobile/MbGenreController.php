<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile-only genre discovery (MB prefix). Returns active genres for the Discover screen.
 */
class MbGenreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->get('limit', 20), 1), 50);

        $genres = Genre::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Genre $genre) => [
                'id' => (string) $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
                'image_url' => $genre->image_url,
                'songs_count' => Song::query()
                    ->where('category_id', $genre->id)
                    ->where('is_active', true)
                    ->count(),
            ]);

        return response()->json(['data' => $genres]);
    }
}
