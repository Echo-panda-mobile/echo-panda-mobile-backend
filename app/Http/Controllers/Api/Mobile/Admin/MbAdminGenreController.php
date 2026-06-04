<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MbAdminGenreController extends Controller
{
    public function index(): JsonResponse
    {
        $genres = Genre::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Genre $genre) => $this->serializeGenre($genre));

        return response()->json(['data' => $genres]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191', 'unique:genres,name'],
        ]);

        $genre = Genre::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json($this->serializeGenre($genre), 201);
    }

    public function update(Request $request, Genre $genre): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('genres', 'name')->ignore($genre->id)],
        ]);

        $genre->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json($this->serializeGenre($genre->fresh()));
    }

    public function destroy(Genre $genre): JsonResponse
    {
        $genre->delete();

        return response()->json(['message' => 'Genre deleted']);
    }

    protected function serializeGenre(Genre $genre): array
    {
        return [
            'id' => $genre->id,
            'name' => $genre->name,
            'slug' => $genre->slug,
            'songs_count' => Song::query()->where('category_id', $genre->id)->count(),
        ];
    }
}
