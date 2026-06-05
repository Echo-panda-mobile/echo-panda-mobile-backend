<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\CatalogImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MbAdminTagController extends Controller
{
    public function __construct(
        protected CatalogImageService $catalogImages
    ) {}
    public function index(): JsonResponse
    {
        $tags = Tag::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Tag $tag) => $this->serializeTag($tag));

        return response()->json(['data' => $tags]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191', 'unique:tags,name'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $tag = Tag::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => $validated['is_active'] ?? true,
            'show_as_row' => $validated['show_as_row'] ?? false,
        ]);

        return response()->json($this->serializeTag($tag), 201);
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:191', Rule::unique('tags', 'name')->ignore($tag->id)],
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
            $tag->update($updates);
            $tag->refresh();
        }

        if (array_key_exists('image_url', $validated)) {
            $this->catalogImages->attachImage($tag, $validated['image_url']);
        }

        return response()->json($this->serializeTag($tag->fresh()));
    }

    public function updateStatus(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $tag->update(['is_active' => $validated['is_active']]);

        return response()->json($this->serializeTag($tag->fresh()));
    }

    public function updateShowAsRow(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'show_as_row' => ['required', 'boolean'],
        ]);

        $tag->update(['show_as_row' => $validated['show_as_row']]);

        return response()->json($this->serializeTag($tag->fresh()));
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(['message' => 'Tag deleted']);
    }

    protected function serializeTag(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'image_url' => $tag->image_url,
            'is_active' => (bool) ($tag->is_active ?? true),
            'show_as_row' => (bool) ($tag->show_as_row ?? false),
            'songs_count' => 0,
        ];
    }
}
