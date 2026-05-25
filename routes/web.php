<?php

use App\Models\Album;
use App\Models\Artist;
use App\Models\FeaturedItem;
use App\Models\Favorite;
use App\Models\ListenHistory;
use App\Models\Report;
use App\Models\Song;
use App\Models\User;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\ArtistController as AdminArtistController;
use App\Http\Controllers\Admin\FeaturedController as AdminFeaturedController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\AlbumController as AdminAlbumController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SongController as AdminSongController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/dashboard');
Route::get('/dashboard', function () {
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
        ],
        'moderation' => [
            'reports_open' => Report::count(),
            'featured_items' => FeaturedItem::count(),
            'favorites_total' => Favorite::count(),
        ],
        'listening' => [
            'listen_events' => ListenHistory::count(),
            'completed_listens' => ListenHistory::where('completed', true)->count(),
            'today_listens' => ListenHistory::whereDate('created_at', today())->count(),
            'minutes_listened' => (int) round((ListenHistory::sum('duration_listened') ?: 0) / 60),
        ],
        'recent_growth' => $recentDays,
        'most_favorited_songs' => $mostFavoritedSongs,
        'trending_artists' => $trendingArtists,
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
        Route::resource('artists', AdminArtistController::class);
        Route::resource('users', AdminUserController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::resource('reports', AdminReportController::class)->only(['index', 'show', 'destroy']);
        Route::resource('genres', AdminGenreController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('featured', AdminFeaturedController::class)->only(['index', 'store', 'destroy']);
        Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::resource('products', AdminProductController::class);
        Route::resource('albums', AdminAlbumController::class);
        Route::resource('songs', AdminSongController::class);
    });
});

require __DIR__.'/auth.php';
