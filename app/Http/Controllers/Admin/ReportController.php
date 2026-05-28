<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\Song;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\Report::class);

        $openReports = Report::query()
            ->where('status', 'open')
            ->with(['reportable', 'user'])
            ->latest()
            ->get();

        $songReports = $openReports
            ->filter(fn (Report $report) => $report->reportable_type === Song::class)
            ->groupBy('reportable_id')
            ->map(function ($reports, $songId) {
                $song = $reports->first()?->reportable;

                return [
                    'id' => (int) $songId,
                    'title' => $song?->title ?? 'Untitled Song',
                    'artist' => $song?->artist ?: $song?->album?->artist,
                    'album' => $song?->album?->title,
                    'is_active' => (bool) ($song?->is_active ?? false),
                    'play_count' => (int) ($song?->play_count ?? 0),
                    'reports' => $reports->map(fn (Report $report) => [
                        'id' => $report->id,
                        'reason' => $report->reason,
                        'details' => $report->details,
                        'status' => $report->status,
                        'reporter' => $report->user?->name,
                        'created_at' => $report->created_at,
                    ])->values(),
                ];
            })
            ->values();

        $albumReports = $openReports
            ->filter(fn (Report $report) => $report->reportable_type === Album::class)
            ->groupBy('reportable_id')
            ->map(function ($reports, $albumId) {
                $album = $reports->first()?->reportable;

                return [
                    'id' => (int) $albumId,
                    'title' => $album?->title ?? 'Untitled Album',
                    'artist' => $album?->artist,
                    'release_status' => $album?->release_status,
                    'songs_count' => (int) ($album?->songs->count() ?? 0),
                    'reports' => $reports->map(fn (Report $report) => [
                        'id' => $report->id,
                        'reason' => $report->reason,
                        'details' => $report->details,
                        'status' => $report->status,
                        'reporter' => $report->user?->name,
                        'created_at' => $report->created_at,
                    ])->values(),
                ];
            })
            ->values();

        return Inertia::render('Admin/Moderation/Index', [
            'songReports' => $songReports,
            'albumReports' => $albumReports,
            'openReportsCount' => $openReports->count(),
        ]);
    }

    public function show(Report $report): Response
    {
        $this->authorize('view', $report);
        return Inertia::render('Admin/Reports/Show', ['report' => $report]);
    }

    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);
        $report->delete();

        return back()->with('success', 'Report removed');
    }
}
