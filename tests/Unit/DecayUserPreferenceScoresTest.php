<?php

namespace Tests\Unit;

use App\Jobs\DecayUserPreferenceScores;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecayUserPreferenceScoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_decay_job_reduces_scores_by_one_percent_daily(): void
    {
        $user = User::factory()->create();

        UserPreference::query()->create([
            'user_id' => $user->id,
            'preference_type' => 'genre',
            'preference_value' => 'Pop',
            'genre' => 'Pop',
            'preference_score' => 100,
        ]);

        (new DecayUserPreferenceScores())->handle();

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preference_type' => 'genre',
            'preference_value' => 'Pop',
            'preference_score' => 99,
        ]);
    }
}
