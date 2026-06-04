<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    /**
     * Display a listing of genres.
     */
    public function index(): JsonResponse
    {
        $genres = Genre::query()
            ->orderBy('name')
            ->get()
            ->map(fn(Genre $genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
                'created_at' => $genre->created_at,
                'updated_at' => $genre->updated_at,
            ]);

        return response()->json([
            'data' => $genres,
        ]);
    }
}
