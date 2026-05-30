<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Song;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MbFavoriteController extends Controller
{
    /**
     * Mobile library: liked songs with normalized song payloads.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->get('per_page', 100), 100);

        $favorites = $user->favorites()
            ->where('favoritable_type', Song::class)
            ->with(['favoritable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Song::class => ['album', 'artistModel'],
                ]);
            }])
            ->latest()
            ->paginate($perPage);

        $favorites->setCollection(
            $favorites->getCollection()->map(function (Favorite $favorite) {
                $song = $favorite->favoritable;

                return [
                    'id' => $favorite->id,
                    'favoritable_type' => $favorite->favoritable_type,
                    'favoritable_id' => $favorite->favoritable_id,
                    'song' => $song instanceof Song ? MbSongTransformer::transform($song) : null,
                ];
            })->filter(fn (array $row) => $row['song'] !== null)->values()
        );

        return response()->json($favorites);
    }
}
