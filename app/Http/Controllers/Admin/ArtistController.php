<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArtistController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\Artist::class);
        $artists = Artist::latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Artists/Index', [
            'artists' => $artists,
        ]);
    }

    public function show(Artist $artist): Response
    {
        $this->authorize('view', $artist);
        $artist->load('songs', 'albums');

        return Inertia::render('Admin/Artists/Show', [
            'artist' => $artist,
        ]);
    }

    public function edit(Artist $artist): Response
    {
        $this->authorize('update', $artist);
        return Inertia::render('Admin/Artists/Edit', ['artist' => $artist]);
    }

    public function update(Request $request, Artist $artist)
    {
        $this->authorize('update', $artist);
        $artist->update($request->only(['name', 'bio', 'verified', 'active']));

        return redirect()->route('admin.albums.index')->with('success', 'Artist updated');
    }

    public function destroy(Artist $artist)
    {
        $this->authorize('delete', $artist);
        $artist->delete();

        return redirect()->route('admin.albums.index')->with('success', 'Artist deleted');
    }
}
