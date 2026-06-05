<?php

namespace App\Jobs;

use App\Models\UserPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DecayUserPreferenceScores implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        UserPreference::query()
            ->where('preference_score', '>', 0)
            ->chunkById(500, function ($preferences) {
                foreach ($preferences as $preference) {
                    $next = (int) floor(((int) $preference->preference_score) * 0.99);
                    $preference->preference_score = max(0, $next);
                    $preference->save();
                }
            });
    }
}
