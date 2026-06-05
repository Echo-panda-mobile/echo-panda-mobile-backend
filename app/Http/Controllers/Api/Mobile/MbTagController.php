<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile-only tag discovery (MB prefix). Returns active tags for the Discover screen.
 */
class MbTagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->get('limit', 20), 1), 50);

        $tags = Tag::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Tag $tag) => [
                'id' => (string) $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'image_url' => $tag->image_url,
                'songs_count' => Song::query()
                    ->where('tag_id', $tag->id)
                    ->where('is_active', true)
                    ->count(),
            ]);

        return response()->json(['data' => $tags]);
    }
}
