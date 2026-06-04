<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneratedPlaylist;
use App\Models\PlaylistPrompt;
use App\Services\AiPromptParser;
use App\Services\PlaylistGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiPlaylistController extends Controller
{
    public function __construct(
        protected AiPromptParser $parser,
        protected PlaylistGenerator $generator
    ) {}

    /**
     * POST /api/ai-playlists/generate
     */
    public function generate(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:500']);
        $user = $request->user();
        $promptText = $request->prompt;

        // 1. Extract criteria via AI
        $criteria = $this->parser->parse($promptText);

        // 2. Generate ranked songs
        $songs = $this->generator->generate($criteria, $user);

        if ($songs->isEmpty()) {
            return response()->json(['message' => 'No matching songs found for this prompt.'], 404);
        }

        // 3. Save the playlist
        $playlist = DB::transaction(function() use ($user, $promptText, $criteria, $songs) {
            $generated = GeneratedPlaylist::create([
                'user_id' => $user->id,
                'title' => $this->formatTitle($promptText, $criteria),
                'prompt' => $promptText,
                'extracted_criteria' => $criteria,
                'cover_url' => $songs->first()->cover_url // Use first song cover as default
            ]);

            // Attach songs with position
            $syncData = [];
            foreach ($songs as $index => $song) {
                $syncData[$song->id] = ['position' => $index + 1];
            }
            $generated->songs()->sync($syncData);

            // Log prompt
            PlaylistPrompt::create([
                'user_id' => $user->id,
                'prompt' => $promptText,
                'generated_playlist_id' => $generated->id
            ]);

            return $generated;
        });

        return response()->json([
            'playlist_id' => $playlist->id,
            'title' => $playlist->title,
            'criteria' => $criteria,
            'songs' => $playlist->songs()->with('artistModel')->get()
        ]);
    }

    public function index(Request $request)
    {
        return GeneratedPlaylist::where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }

    public function show(GeneratedPlaylist $playlist)
    {
        if ($playlist->user_id !== Auth::id()) abort(403);

        return response()->json([
            'playlist' => $playlist,
            'songs' => $playlist->songs()->with('artistModel')->get()
        ]);
    }

    public function destroy(GeneratedPlaylist $playlist)
    {
        if ($playlist->user_id !== Auth::id()) abort(403);
        $playlist->delete();
        return response()->json(['message' => 'Playlist deleted']);
    }

    private function formatTitle(string $prompt, array $criteria): string
    {
        // Simple heuristic for title: "Genre/Mood Mix" or capitalized prompt snippet
        $mood = $criteria['mood'] ?? null;
        $genre = $criteria['genre'] ?? null;

        if ($mood && $genre) return "$mood $genre AI Mix";
        if ($genre) return "$genre AI Selection";
        if ($mood) return "$mood AI Vibes";

        return Str::limit(ucfirst($prompt), 27) . ' (AI)';
    }
}
