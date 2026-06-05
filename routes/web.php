<?php

use App\Models\Album;
use App\Models\Artist;
use App\Models\Favorite;
use App\Models\ListenHistory;
use App\Models\Report;
use App\Models\Song;
use App\Models\User;
use App\Models\Genre;
use App\Models\Tag;
use App\Http\Controllers\Admin\ArtistController as AdminArtistController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\AlbumController as AdminAlbumController;
use App\Http\Controllers\Admin\SongController as AdminSongController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

Route::redirect('/', '/dashboard');
Route::get('/dashboard', function () {
    $latestUsers = User::query()
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ])
        ->values();

    $latestArtists = Artist::query()
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn (Artist $artist) => [
            'id' => $artist->id,
            'name' => $artist->name,
            'slug' => $artist->slug,
            'is_active' => (bool) $artist->is_active,
            'created_at' => $artist->created_at,
        ])
        ->values();

    $latestSongs = Song::query()
        ->with('album')
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn (Song $song) => [
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artist ?: $song->artistModel?->name,
            'album' => $song->album?->title,
            'play_count' => (int) $song->play_count,
            'created_at' => $song->created_at,
        ])
        ->values();

    $latestAlbums = Album::query()
        ->with('artistModel')
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn (Album $album) => [
            'id' => $album->id,
            'title' => $album->title,
            'artist' => $album->artist ?: $album->artistModel?->name,
            'release_status' => $album->release_status,
            'created_at' => $album->created_at,
        ])
        ->values();

    $recentDays = collect(range(6, 0))->map(function (int $offset) {
        $date = now()->subDays($offset)->startOfDay();

        return [
            'label' => $date->format('M j'),
            'users' => User::whereDate('created_at', $date)->count(),
            'artists' => Artist::whereDate('created_at', $date)->count(),
            'songs' => Song::whereDate('created_at', $date)->count(),
        ];
    })->values();

    $mostFavoritedSongs = Song::query()
        ->withCount('favorites')
        ->with(['album.artistModel'])
        ->orderByDesc('favorites_count')
        ->orderByDesc('play_count')
        ->limit(5)
        ->get()
        ->map(fn (Song $song) => [
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artist ?: $song->album?->artist,
            'album' => $song->album?->title,
            'favorites_count' => (int) $song->favorites_count,
            'play_count' => (int) $song->play_count,
        ])
        ->values();

    $mostPlayedSong = Song::query()
        ->with(['album.artistModel'])
        ->orderByDesc('play_count')
        ->first();

    $favoriteArtistGroups = Song::query()
        ->withCount('favorites')
        ->with(['artistModel'])
        ->get()
        ->groupBy(fn (Song $song) => $song->artist_id ?: $song->artist ?: $song->id);

    $mostFavoriteArtist = $favoriteArtistGroups
        ->map(function ($songs) {
            $first = $songs->first();

            return [
                'id' => $first?->artist_id,
                'name' => $first?->artistModel?->name ?: $first?->artist ?: 'Unknown Artist',
                'favorites_count' => (int) $songs->sum('favorites_count'),
                'songs_count' => $songs->count(),
            ];
        })
        ->sortByDesc('favorites_count')
        ->values()
        ->first();

    $trendingArtists = Artist::query()
        ->withCount(['songs', 'albums'])
        ->withSum('songs', 'play_count')
        ->orderByDesc('songs_sum_play_count')
        ->orderByDesc('songs_count')
        ->limit(5)
        ->get()
        ->map(fn (Artist $artist) => [
            'id' => $artist->id,
            'name' => $artist->name,
            'songs_count' => (int) $artist->songs_count,
            'albums_count' => (int) $artist->albums_count,
            'play_count' => (int) ($artist->songs_sum_play_count ?? 0),
        ])
        ->values();

    $topGenres = Genre::query()
        ->withCount('songs')
        ->orderByDesc('songs_count')
        ->limit(5)
        ->get()
        ->map(fn (Genre $genre) => [
            'id' => $genre->id,
            'name' => $genre->name,
            'songs_count' => $genre->songs_count,
        ]);

    $dashboardMetrics = [
        'totals' => [
            'total_users' => User::count(),
            'total_admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'total_artists' => Artist::count(),
            'active_artists' => Artist::where('is_active', true)->count(),
            'total_songs' => Song::count(),
            'active_songs' => Song::where('is_active', true)->count(),
            'total_albums' => Album::count(),
            'published_albums' => Album::where('release_status', 'published')->count(),
            'total_categories' => Genre::count(),
            'total_tags' => Schema::hasTable('tags') ? Tag::count() : 0,
        ],
        'moderation' => [
            'reports_open' => Report::count(),
            'favorites_total' => Favorite::count(),
            'featured_items' => Schema::hasTable('featured_items')
                ? DB::table('featured_items')->count()
                : 0,
        ],
        'listening' => [
            'listen_events' => ListenHistory::count(),
            'completed_listens' => ListenHistory::where('completed', true)->count(),
            'today_listens' => ListenHistory::whereDate('created_at', today())->count(),
            'minutes_listened' => (int) round((ListenHistory::sum('duration_listened') ?: 0) / 60),
            'total_streams' => Song::sum('play_count'),
            'new_users_this_month' => User::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
        ],
        'recent_growth' => $recentDays,
        'most_favorited_songs' => $mostFavoritedSongs,
        'most_played_song' => $mostPlayedSong ? [
            'id' => $mostPlayedSong->id,
            'title' => $mostPlayedSong->title,
            'artist' => $mostPlayedSong->artist ?: $mostPlayedSong->artistModel?->name,
            'album' => $mostPlayedSong->album?->title,
            'play_count' => (int) $mostPlayedSong->play_count,
        ] : null,
        'most_favorite_artist' => $mostFavoriteArtist,
        'trending_artists' => $trendingArtists,
        'top_genres' => $topGenres,
        'latest_users' => $latestUsers,
        'latest_artists' => $latestArtists,
        'latest_songs' => $latestSongs,
        'latest_albums' => $latestAlbums,
    ];

    return Inertia::render('Dashboard', [
        'metrics' => $dashboardMetrics,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Legacy alias kept for older frontend bundles that still call route('admin.analytics.index').
        Route::get('analytics', function () {
            return redirect()->route('dashboard');
        })->name('analytics.index');

        // Legacy alias kept for older frontend bundles that still call route('admin.featured.index').
        Route::get('featured', [AdminReportController::class, 'index'])->name('featured.index');
        Route::resource('artists', AdminArtistController::class);
        Route::resource('users', AdminUserController::class)->only(['index', 'show', 'create', 'update', 'destroy']);
        Route::post('users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
        Route::post('users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
        Route::resource('reports', AdminReportController::class)->only(['index', 'show', 'destroy']);
        Route::post('reports/{report}/action', [AdminReportController::class, 'action'])->name('reports.action');
        Route::get('moderation', [AdminReportController::class, 'index'])->name('moderation.index');
        Route::resource('genres', AdminGenreController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('tags', AdminTagController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('albums', AdminAlbumController::class);
        Route::post('albums/{album}/approve', [AdminAlbumController::class, 'approve'])->name('albums.approve');
        Route::post('albums/{album}/hide', [AdminAlbumController::class, 'hide'])->name('albums.hide');
        Route::post('albums/{album}/report', [AdminAlbumController::class, 'report'])->name('albums.report');
        Route::post('albums/bulk-moderate', [AdminAlbumController::class, 'bulkModerate'])->name('albums.bulk-moderate');
        Route::resource('songs', AdminSongController::class);
        Route::post('songs/{song}/approve', [AdminSongController::class, 'approve'])->name('songs.approve');
        Route::post('songs/{song}/hide', [AdminSongController::class, 'hide'])->name('songs.hide');
        Route::post('songs/{song}/report', [AdminSongController::class, 'report'])->name('songs.report');
        Route::post('songs/bulk-moderate', [AdminSongController::class, 'bulkModerate'])->name('songs.bulk-moderate');
    });
});

require __DIR__.'/auth.php';
