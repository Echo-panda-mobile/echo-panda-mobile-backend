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
        $query = Album::query()->withCount('songs');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $albums = $query->latest()->paginate(15)->withQueryString();
        $this->attachModerationReportData($albums->getCollection());

        return Inertia::render('Admin/Albums/Index', [
            'albums' => $albums,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Albums/Create', [
            'artists' => Artist::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAlbumRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $artist = Artist::findOrFail($validated['artist_id']);

        Album::create([
            'artist_id' => $artist->id,
            'artist' => $artist->name,
            'title' => $validated['title'],
            'release_date' => $validated['release_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'release_status' => $validated['release_status'] ?? 'draft',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'cover_key' => $validated['cover_key'] ?? null,
        ]);

        return redirect()->route('admin.albums.index')
            ->with('success', 'Album created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Album $album): Response
    {
        $album->load('songs');

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
        $artist = Artist::findOrFail($validated['artist_id']);

        $album->update([
            'artist_id' => $artist->id,
            'artist' => $artist->name,
            'title' => $validated['title'],
            'release_date' => $validated['release_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'release_status' => $validated['release_status'] ?? $album->release_status,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'cover_key' => $validated['cover_key'] ?? null,
        ]);

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

    public function approve(Album $album): RedirectResponse
    {
        $album->update(['release_status' => 'published']);

        return back()->with('success', 'Album approved and published.');
    }

    public function hide(Album $album): RedirectResponse
    {
        $album->update(['release_status' => 'rejected']);

        return back()->with('success', 'Album hidden from the platform.');
    }

    public function report(Request $request, Album $album): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:5000'],
        ]);

        Report::create([
            'reportable_type' => Album::class,
            'reportable_id' => $album->id,
            'user_id' => $request->user()?->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('success', 'Album reported for moderation review.');
    }

    public function bulkModerate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,hide'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:albums,id'],
        ]);

        $updates = [
            'approve' => ['release_status' => 'published'],
            'hide' => ['release_status' => 'rejected'],
        ];

        Album::whereIn('id', $validated['ids'])->update($updates[$validated['action']]);

        return back()->with('success', ucfirst($validated['action']).'d selected albums.');
    }

    protected function attachModerationReportData(SupportCollection $albums): void
    {
        if ($albums->isEmpty()) {
            return;
        }

        $reportsByAlbum = Report::query()
            ->where('reportable_type', Album::class)
            ->whereIn('reportable_id', $albums->pluck('id'))
            ->with('user')
            ->latest()
            ->get()
            ->groupBy('reportable_id');

        $albums->each(function (Album $album) use ($reportsByAlbum) {
            $reports = $reportsByAlbum->get($album->id, collect());

            $album->setAttribute('report_count', $reports->count());
            $album->setAttribute('open_report_count', $reports->where('status', 'open')->count());
            $album->setAttribute('recent_reports', $reports->take(5)->map(fn (Report $report) => [
                'id' => $report->id,
                'reason' => $report->reason,
                'details' => $report->details,
                'status' => $report->status,
                'reporter' => $report->user?->name,
                'created_at' => $report->created_at,
            ])->values());
        });
    }
}
