<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GenreController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\Genre::class);
        $genres = Genre::orderBy('name')->get();

        return Inertia::render('Admin/Genres/Index', ['genres' => $genres]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Genre::class);
        $request->validate(['name' => 'required|string|max:191']);

        Genre::create($request->only('name'));

        return back()->with('success', 'Genre created');
    }

    public function update(Request $request, Genre $genre)
    {
        $this->authorize('update', $genre);
        $genre->update($request->only('name'));

        return back()->with('success', 'Genre updated');
    }

    public function destroy(Genre $genre)
    {
        $this->authorize('delete', $genre);
        $genre->delete();

        return back()->with('success', 'Genre deleted');
    }
}
