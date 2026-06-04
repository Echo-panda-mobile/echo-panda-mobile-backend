<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Song;
use App\Models\Album;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:song,album,artist',
            'id' => 'required|integer',
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        $reportableType = null;
        if ($validated['type'] === 'song') $reportableType = Song::class;
        elseif ($validated['type'] === 'album') $reportableType = Album::class;
        elseif ($validated['type'] === 'artist') $reportableType = Artist::class;

        $item = $reportableType::find($validated['id']);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $report = Report::create([
            'user_id' => Auth::id(),
            'reportable_type' => $reportableType,
            'reportable_id' => $validated['id'],
            'reason' => $validated['reason'],
            'details' => $validated['details'],
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Report submitted successfully. Our moderation team will review it.',
            'report' => $report
        ], 201);
    }
}
