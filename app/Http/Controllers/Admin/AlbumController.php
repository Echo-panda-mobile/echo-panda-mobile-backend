<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Report;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Album::query()->with(['artistModel'])->withCount('songs');

        // Stats calculation
        $stats = [
            'total' => Album::count(),
            'active' => Album::where('release_status', 'published')->count(),
            'deleted' => Album::onlyTrashed()->count(),
        ];

        // Filter by Status
        $status = $request->get('status');
        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'active') {
            $query->where('release_status', 'published');
        } else {
            $query->withTrashed();
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
                    ->orWhereHas('artistModel', function($aq) use ($search) {
                        $aq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $albums = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Albums/Index', [
            'albums' => $albums,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * DISABLED: Admin cannot create albums directly.
     */
    public function create(): Response
    {
        abort(403, 'Administrators cannot create albums directly. Albums must be created by artists.');
    }

    /**
     * Store a newly created resource in storage.
     * DISABLED: Admin cannot create albums directly.
     */
    public function store(StoreAlbumRequest $request): RedirectResponse
    {
        abort(403, 'Administrators cannot create albums directly. Albums must be created by artists.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Album $album): Response
    {
        $album->load(['songs', 'artistModel']);

        return Inertia::render('Admin/Albums/Show', [
            'album' => $album,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Album $album): Response
    {
        return Inertia::render('Admin/Albums/Edit', [
            'album' => $album,
            'artists' => Artist::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAlbumRequest $request, Album $album): RedirectResponse
    {
        $validated = $request->validated();

        $album->update($validated);

        return redirect()->route('admin.albums.index')
            ->with('success', 'Album updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Album $album): RedirectResponse
    {
        $album->delete();

        return redirect()->route('admin.albums.index')
            ->with('success', 'Album deleted successfully.');
    }

    /**
     * Restore a deleted album.
     */
    public function restore($id): RedirectResponse
    {
        $album = Album::withTrashed()->findOrFail($id);
        $album->restore();

        return back()->with('success', 'Album restored successfully.');
    }

    /**
     * Permanent delete.
     */
    public function forceDelete($id): RedirectResponse
    {
        $album = Album::withTrashed()->findOrFail($id);
        $album->forceDelete();

        return redirect()->route('admin.albums.index')
            ->with('success', 'Album permanently removed.');
    }
}
