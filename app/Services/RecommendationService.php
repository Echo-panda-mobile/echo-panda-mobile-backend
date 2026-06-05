<?php

namespace App\Services;

use App\Models\Song;
use App\Models\User;
use App\Models\GeneratedPlaylist;
use App\Models\UserInteraction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function __construct(
        protected PlaylistGenerator $generator
    ) {}

    /**
     * Daily Mix: Based on user's recent history, liked songs, and follows.
     */
    public function generateDailyMix(User $user, int $mixNumber = 1): GeneratedPlaylist
    {
        // Get user's top genres from history
        $topGenres = DB::table('user_listen_history')
            ->join('songs', 'user_listen_history.song_id', '=', 'songs.id')
            ->join('genres', 'songs.genre_id', '=', 'genres.id')
            ->where('user_listen_history.user_id', $user->id)
            ->groupBy('genres.name')
            ->orderByRaw('SUM(play_count) DESC')
            ->limit(3)
            ->pluck('genres.name')
            ->toArray();

        // Get followed artists
        $followedArtists = DB::table('artist_followers')
            ->join('artists', 'artist_followers.artist_id', '=', 'artists.id')
            ->where('artist_followers.user_id', $user->id)
            ->pluck('artists.name')
            ->toArray();

        $criteria = [
            'genres' => $topGenres,
            'artists' => $followedArtists,
            'user_id' => $user->id
        ];

        $songs = $this->generator->generate($criteria, $user, 30);

        return $this->savePlaylist($user, "Daily Mix #$mixNumber", "Your daily dose of music based on your taste.", $songs, 'daily_mix', $criteria);
    }

    /**
     * Discover Weekly: Songs user hasn't heard, but match their preferred genres/artists.
     */
    public function generateDiscoverWeekly(User $user): GeneratedPlaylist
    {
        // Genres user likes
        $preferredGenres = DB::table('user_listen_history')
            ->join('songs', 'user_listen_history.song_id', '=', 'songs.id')
            ->join('genres', 'songs.genre_id', '=', 'genres.id')
            ->where('user_listen_history.user_id', $user->id)
            ->groupBy('genres.name')
            ->orderByRaw('SUM(play_count) DESC')
            ->limit(5)
            ->pluck('genres.name')
            ->toArray();

        // Exclude songs user already played
        $playedSongIds = DB::table('user_listen_history')
            ->where('user_id', $user->id)
            ->pluck('song_id')
            ->toArray();

        $criteria = [
            'genres' => $preferredGenres,
            'exclude_ids' => $playedSongIds,
            'discovery_mode' => true
        ];

        $songs = $this->generator->generate($criteria, $user, 30);

        return $this->savePlaylist($user, "Discover Weekly", "Fresh finds tailored to you.", $songs, 'discover_weekly', $criteria);
    }

    /**
     * Trending Now: Popular songs globally.
     */
    public function generateTrendingPlaylist(?User $user = null): GeneratedPlaylist
    {
        $songs = Song::query()
            ->where('is_active', true)
            ->orderBy('play_count', 'desc')
            ->limit(50)
            ->get();

        return $this->savePlaylist($user, "Trending Now", "The hottest tracks on Echo Panda right now.", $songs, 'trending');
    }

    /**
     * Helper to persist generated playlist.
     */
    private function savePlaylist(?User $user, string $title, string $prompt, Collection $songs, string $type, array $criteria = []): GeneratedPlaylist
    {
        $playlist = GeneratedPlaylist::create([
            'user_id' => $user?->id ?? 1, // Default to admin/system if no user
            'title' => $title,
            'prompt' => $prompt,
            'type' => $type,
            'extracted_criteria' => $criteria,
            'cover_url' => $songs->first()?->songCover_url ?? null,
        ]);

        foreach ($songs as $index => $song) {
            GeneratedPlaylistSong::create([
                'playlist_id' => $playlist->id,
                'song_id' => $song->id,
                'position' => $index,
            ]);
        }

        return $playlist;
    }
}
