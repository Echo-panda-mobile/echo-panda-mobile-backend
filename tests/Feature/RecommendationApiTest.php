<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\RecommendationEvent;
use App\Models\Song;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecommendationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendations_endpoint_returns_reason_text_and_tracks_shown_events(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $artist = Artist::query()->create([
            'name' => 'The Weeknd',
            'slug' => Str::slug('The Weeknd').'-'.uniqid(),
        ]);
        $genre = Genre::query()->create(['name' => 'Pop', 'slug' => 'pop']);
        $tag = Tag::query()->create(['name' => 'Night', 'slug' => 'night']);
        $album = Album::query()->create([
            'title' => 'After Hours',
            'artist' => 'The Weeknd',
            'artist_id' => $artist->id,
        ]);

        $song = Song::query()->create([
            'album_id' => $album->id,
            'artist_id' => $artist->id,
            'title' => 'Blinding Lights',
            'artist' => 'The Weeknd',
            'duration' => 180,
            'track_number' => 1,
            'category_id' => $genre->id,
            'mood' => 'Energetic',
            'tag_id' => $tag->id,
            'is_active' => true,
            'play_count' => 120,
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'preference_type' => 'artist',
            'preference_value' => 'The Weeknd',
            'genre' => 'artist:The Weeknd',
            'preference_score' => 80,
        ]);

        $response = $this->getJson('/api/recommendations?limit=5');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'title',
                        'recommendation_score',
                        'recommendation_reason',
                        'song',
                    ],
                ],
                'meta',
            ]);

        $this->assertNotEmpty($response->json('data.0.recommendation_reason'));

        $this->assertDatabaseHas('recommendation_events', [
            'user_id' => $user->id,
            'song_id' => $song->id,
            'event_type' => 'recommendation_shown',
        ]);

        $this->assertGreaterThan(0, RecommendationEvent::query()->count());
    }
}
