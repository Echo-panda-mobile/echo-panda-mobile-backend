<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Song;
use App\Services\CatalogImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MbAdminGenreController extends Controller
{
    public function __construct(
        protected CatalogImageService $catalogImages
    ) {}
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
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $genre = Genre::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => $validated['is_active'] ?? true,
            'show_as_row' => $validated['show_as_row'] ?? false,
        ]);

        return response()->json($this->serializeGenre($genre), 201);
    }

    public function update(Request $request, Genre $genre): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:191', Rule::unique('genres', 'name')->ignore($genre->id)],
            'is_active' => ['sometimes', 'boolean'],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $updates = [];
        if (array_key_exists('name', $validated)) {
            $updates['name'] = $validated['name'];
            $updates['slug'] = Str::slug($validated['name']);
        }
        if (array_key_exists('is_active', $validated)) {
            $updates['is_active'] = $validated['is_active'];
        }

        if ($updates !== []) {
            $genre->update($updates);
            $genre->refresh();
        }

        if (array_key_exists('image_url', $validated)) {
            $this->catalogImages->attachImage($genre, $validated['image_url']);
        }

        return response()->json($this->serializeGenre($genre->fresh()));
    }

    public function updateStatus(Request $request, Genre $genre): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $genre->update(['is_active' => $validated['is_active']]);

        return response()->json($this->serializeGenre($genre->fresh()));
    }

    public function updateShowAsRow(Request $request, Genre $genre): JsonResponse
    {
        $validated = $request->validate([
            'show_as_row' => ['required', 'boolean'],
        ]);

        $genre->update(['show_as_row' => $validated['show_as_row']]);

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
            'image_url' => $genre->image_url,
            'is_active' => (bool) ($genre->is_active ?? true),
            'show_as_row' => (bool) ($genre->show_as_row ?? false),
            'songs_count' => Song::query()->where('category_id', $genre->id)->count(),
        ];
    }
}
