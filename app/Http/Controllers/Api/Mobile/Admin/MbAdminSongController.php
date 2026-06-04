<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MbAdminSongController extends Controller
{
    public function approve(Song $song): JsonResponse
    {
        $song->update(['is_active' => true]);

        return response()->json(['message' => 'Song approved and made visible.']);
    }

    public function hide(Song $song): JsonResponse
    {
        $song->update(['is_active' => false]);

        return response()->json(['message' => 'Song hidden from the platform.']);
    }

    public function report(Request $request, Song $song): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:5000'],
        ]);

        Report::create([
            'reportable_type' => Song::class,
            'reportable_id' => $song->id,
            'user_id' => $request->user()?->id,
            'reason' => $validated['reason'] ?? 'Flagged by admin via mobile app',
            'details' => $validated['details'] ?? null,
            'status' => 'open',
        ]);

        return response()->json(['message' => 'Song reported for moderation review.']);
    }
}
