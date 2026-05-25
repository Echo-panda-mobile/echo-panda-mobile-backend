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
