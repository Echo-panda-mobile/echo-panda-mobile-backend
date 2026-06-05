<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tag::class);
        $tags = Tag::orderBy('name')->get();

        return Inertia::render('Admin/Tags/Index', ['tags' => $tags]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tag::class);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191', 'unique:tags,name'],
        ]);

        Tag::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('success', 'Tag created');
    }

    public function update(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('tags', 'name')->ignore($tag->id)],
        ]);

        $tag->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('success', 'Tag updated');
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);
        $tag->delete();

        return back()->with('success', 'Tag deleted');
    }
}
