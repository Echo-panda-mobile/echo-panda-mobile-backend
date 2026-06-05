<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Album;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('releases:publish-scheduled', function () {
    $updated = Album::query()
        ->whereNotNull('scheduled_at')
        ->where('scheduled_at', '<=', now())
        ->whereIn('release_status', ['draft', 'pending_review'])
        ->update([
            'release_status' => 'published',
            'release_date' => now()->toDateString(),
            'scheduled_at' => null,
            'updated_at' => now(),
        ]);

    $this->info("Published {$updated} scheduled releases.");
})->purpose('Publish artist releases once scheduled_at is reached');

Schedule::command('releases:publish-scheduled')->everyMinute();

// Dynamic Playlists Generation
Artisan::command('recommendations:generate-daily', function () {
    $service = app(\App\Services\RecommendationService::class);
    $users = \App\Models\User::all(); // In production, maybe filter by active users
    foreach ($users as $user) {
        $service->generateDailyMix($user);
    }
})->purpose('Generate daily mixes for users');

Artisan::command('recommendations:generate-weekly', function () {
    $service = app(\App\Services\RecommendationService::class);
    $users = \App\Models\User::all();
    foreach ($users as $user) {
        $service->generateDiscoverWeekly($user);
    }
})->purpose('Generate discover weekly for users');

Artisan::command('recommendations:update-trending', function () {
    $service = app(\App\Services\RecommendationService::class);
    $service->generateTrendingPlaylist();
})->purpose('Update trending playlist');

Schedule::command('recommendations:generate-daily')->dailyAt('04:00');
Schedule::command('recommendations:generate-weekly')->weeklyOn(1, '04:00'); // Mondays
Schedule::command('recommendations:update-trending')->hourly();
