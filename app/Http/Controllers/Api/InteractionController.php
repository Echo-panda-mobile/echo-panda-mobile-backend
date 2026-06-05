<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserInteraction;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    /**
     * Track user behavior: play, pause, skip, complete, like, share, playlist_add.
     */
    public function track(Request $request)
    {
        $request->validate([
            'song_id' => 'required|exists:songs,id',
            'action' => 'required|string|in:play,pause,skip,complete,like,share,playlist_add'
        ]);

        UserInteraction::create([
            'user_id' => $request->user()->id,
            'song_id' => $request->song_id,
            'action' => $request->action,
        ]);

        return response()->json(['status' => 'success']);
    }
}
