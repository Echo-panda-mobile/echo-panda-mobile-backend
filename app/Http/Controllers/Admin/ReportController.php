<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\Song;
use App\Models\Artist;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Report::class);

        $reports = Report::query()
            ->with(['reportable', 'user'])
            ->latest()
            ->get();

        $songReports = $reports
            ->filter(fn (Report $report) => $report->reportable_type === Song::class)
            ->groupBy('reportable_id')
            ->map(fn ($group, $id) => $this->formatReportGroup($group, $id))
            ->values();

        $albumReports = $reports
            ->filter(fn (Report $report) => $report->reportable_type === Album::class)
            ->groupBy('reportable_id')
            ->map(fn ($group, $id) => $this->formatReportGroup($group, $id))
            ->values();

        $artistReports = $reports
            ->filter(fn (Report $report) => $report->reportable_type === Artist::class)
            ->groupBy('reportable_id')
            ->map(fn ($group, $id) => $this->formatReportGroup($group, $id))
            ->values();

        return Inertia::render('Admin/Moderation/Index', [
            'songReports' => $songReports,
            'albumReports' => $albumReports,
            'artistReports' => $artistReports,
            'openReportsCount' => Report::where('status', 'open')->count(),
        ]);
    }

    protected function formatReportGroup($reports, $id)
    {
        $item = $reports->first()?->reportable;
        $type = $reports->first()?->reportable_type;

        $data = [
            'id' => (int) $id,
            'reports' => $reports->map(fn (Report $report) => [
                'id' => $report->id,
                'reason' => $report->reason,
                'details' => $report->details,
                'status' => $report->status,
                'reporter' => $report->user?->name,
                'created_at' => $report->created_at,
            ])->values(),
        ];

        if ($type === Song::class) {
            $data['title'] = $item?->title ?? 'Deleted Song';
            $data['artist'] = $item?->artist ?: $item?->album?->artist;
            $data['is_active'] = (bool) ($item?->is_active ?? false);
        } elseif ($type === Album::class) {
            $data['title'] = $item?->title ?? 'Deleted Album';
            $data['artist'] = $item?->artist;
            $data['release_status'] = $item?->release_status;
        } elseif ($type === Artist::class) {
            $data['name'] = $item?->name ?? 'Deleted Artist';
            $data['is_active'] = (bool) ($item?->is_active ?? false);
        }

        return $data;
    }

    public function action(Request $request, Report $report): RedirectResponse
    {
        $this->authorize('update', $report);

        $action = $request->input('action'); // review, remove, ignore

        if ($action === 'review') {
            $report->update(['status' => 'under_review']);
            return back()->with('success', 'Report marked as under review');
        }

        if ($action === 'ignore') {
            $report->update(['status' => 'ignored']);
            return back()->with('success', 'Report ignored');
        }

        if ($action === 'remove') {
            $item = $report->reportable;
            if ($item) {
                if (method_exists($item, 'delete')) {
                    // For songs/albums/artists, we might want to just deactivate instead of delete
                    if (isset($item->is_active)) {
                        $item->update(['is_active' => false]);
                    } elseif (isset($item->release_status)) {
                        $item->update(['release_status' => 'hidden']);
                    }
                }
            }
            $report->update(['status' => 'resolved']);
            return back()->with('success', 'Content hidden and report resolved');
        }

        return back()->with('error', 'Invalid action');
    }

    public function show(Report $report): Response
    {
        $this->authorize('view', $report);
        return Inertia::render('Admin/Reports/Show', ['report' => $report]);
    }

    public function destroy(Report $report): RedirectResponse
    {
        $this->authorize('delete', $report);
        $report->delete();

        return back()->with('success', 'Report removed from database');
    }
}
