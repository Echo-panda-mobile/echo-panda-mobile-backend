<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', \App\Models\Artist::class);
        // placeholder — real metrics will be computed by services/queries
        $metrics = [
            'total_users' => \App\Models\User::count(),
            'total_artists' => \App\Models\Artist::count(),
            'total_songs' => \App\Models\Song::count(),
        ];

        return Inertia::render('Admin/Analytics/Index', ['metrics' => $metrics]);
    }
}
