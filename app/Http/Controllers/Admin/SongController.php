<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Models\Album;
use App\Models\Report;
use App\Models\Song;
use Illuminate\Support\Collection as SupportCollection;
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
        $query = Song::query()->with('album');

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
        $this->attachModerationReportData($songs->getCollection());
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

    public function approve(Song $song): RedirectResponse
    {
        $song->update(['is_active' => true]);

        return back()->with('success', 'Song approved and made visible.');
    }

    public function hide(Song $song): RedirectResponse
    {
        $song->update(['is_active' => false]);

        return back()->with('success', 'Song hidden from the platform.');
    }

    public function report(Request $request, Song $song): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:5000'],
        ]);

        Report::create([
            'reportable_type' => Song::class,
            'reportable_id' => $song->id,
            'user_id' => $request->user()?->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('success', 'Song reported for moderation review.');
    }

    public function bulkModerate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,hide'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:songs,id'],
        ]);

        $updates = [
            'approve' => ['is_active' => true],
            'hide' => ['is_active' => false],
        ];

        Song::whereIn('id', $validated['ids'])->update($updates[$validated['action']]);

        return back()->with('success', ucfirst($validated['action']).'d selected songs.');
    }

    protected function attachModerationReportData(SupportCollection $songs): void
    {
        if ($songs->isEmpty()) {
            return;
        }

        $reportsBySong = Report::query()
            ->where('reportable_type', Song::class)
            ->whereIn('reportable_id', $songs->pluck('id'))
            ->with('user')
            ->latest()
            ->get()
            ->groupBy('reportable_id');

        $songs->each(function (Song $song) use ($reportsBySong) {
            $reports = $reportsBySong->get($song->id, collect());

            $song->setAttribute('report_count', $reports->count());
            $song->setAttribute('open_report_count', $reports->where('status', 'open')->count());
            $song->setAttribute('recent_reports', $reports->take(5)->map(fn (Report $report) => [
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
