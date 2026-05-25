<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\Report::class);
        $reports = Report::latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports,
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
