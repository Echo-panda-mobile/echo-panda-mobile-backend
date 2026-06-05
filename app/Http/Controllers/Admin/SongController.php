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
        $query = Song::query()->with(['album', 'artistModel']);

        // Stats calculation
        $stats = [
            'total' => Song::count(),
            'active' => Song::where('is_active', true)->count(),
            'reported' => Song::whereHas('reports', function($q) { $q->where('status', 'open'); })->count(),
            'deleted' => Song::onlyTrashed()->count(),
        ];

        // Filter by Status
        $status = $request->get('status');
        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'reported') {
            $query->whereHas('reports', function($q) { $q->where('status', 'open'); });
        } else {
            $query->withTrashed();
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
                    ->orWhereHas('album', function($aq) use ($search) {
                        $aq->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('artistModel', function($arq) use ($search) {
                        $arq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('album_id') && $request->get('album_id')) {
            $query->where('album_id', $request->get('album_id'));
        }

        $songs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $this->attachModerationReportData($songs->getCollection());
        $albums = Album::select('id', 'title')->get();

        return Inertia::render('Admin/Songs/Index', [
            'songs' => $songs,
            'albums' => $albums,
            'stats' => $stats,
            'filters' => $request->only(['search', 'album_id', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * DISABLED: Admin cannot create songs directly.
     */
    public function create(Request $request): Response
    {
        abort(403, 'Administrators cannot create songs directly. Songs must be uploaded by artists.');
    }

    /**
     * Store a newly created resource in storage.
     * DISABLED: Admin cannot create songs directly.
     */
    public function store(StoreSongRequest $request): RedirectResponse
    {
        abort(403, 'Administrators cannot create songs directly. Songs must be uploaded by artists.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Song $song): Response
    {
        $song->load(['album', 'artistModel', 'reports.user']);

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

    /**
     * Permanent delete for moderation.
     */
    public function forceDelete($id): RedirectResponse
    {
        $song = Song::withTrashed()->findOrFail($id);
        $song->forceDelete();

        return redirect()->route('admin.songs.index')
            ->with('success', 'Song permanently removed from catalog.');
    }

    /**
     * Restore a deleted song.
     */
    public function restore($id): RedirectResponse
    {
        $song = Song::withTrashed()->findOrFail($id);
        $song->restore();

        return back()->with('success', 'Song restored successfully.');
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
            $song->setAttribute('recent_reports', $reports->take(10)->map(fn (Report $report) => [
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
