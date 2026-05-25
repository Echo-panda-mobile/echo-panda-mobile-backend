<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Song::with('album');

        // Filter by album
        if ($request->has('album_id')) {
            $query->where('album_id', $request->get('album_id'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%");
            });
        }

        $songs = $query->orderBy('track_number')->paginate(15)->withQueryString();
        $albums = Album::all();

        return Inertia::render('Admin/Songs/Index', [
            'songs' => $songs,
            'albums' => $albums,
            'filters' => $request->only(['search', 'album_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $albums = Album::all();
        $albumId = $request->get('album_id');

        return Inertia::render('Admin/Songs/Create', [
            'albums' => $albums,
            'defaultAlbumId' => $albumId,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSongRequest $request): RedirectResponse
    {
        Song::create($request->validated());

        return redirect()->route('admin.songs.index')
            ->with('success', 'Song created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Song $song): Response
    {
        $song->load('album');

        return Inertia::render('Admin/Songs/Show', [
            'song' => $song,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Song $song): Response
    {
        $albums = Album::all();

        return Inertia::render('Admin/Songs/Edit', [
            'song' => $song,
            'albums' => $albums,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSongRequest $request, Song $song): RedirectResponse
    {
        $song->update($request->validated());

        return redirect()->route('admin.songs.index')
            ->with('success', 'Song updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Song $song): RedirectResponse
    {
        $song->delete();

        return redirect()->route('admin.songs.index')
            ->with('success', 'Song deleted successfully.');
    }
}
