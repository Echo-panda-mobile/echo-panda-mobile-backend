<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Song;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecommendationExtraEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_similar_and_cold_start_endpoints_work_and_event_tracking_endpoint_accepts_payload(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $artist = Artist::query()->create([
            'name' => 'Artist A',
            'slug' => Str::slug('Artist A').'-'.uniqid(),
        ]);
        $genre = Genre::query()->create(['name' => 'Pop', 'slug' => 'pop']);
        $tag = Tag::query()->create(['name' => 'Vibe', 'slug' => 'vibe']);
        $album = Album::query()->create([
            'title' => 'Album A',
            'artist' => 'Artist A',
            'artist_id' => $artist->id,
        ]);

        $seedSong = Song::query()->create([
            'album_id' => $album->id,
            'artist_id' => $artist->id,
            'title' => 'Seed',
            'artist' => 'Artist A',
            'duration' => 180,
            'track_number' => 1,
            'category_id' => $genre->id,
            'mood' => 'Energetic',
            'tag_id' => $tag->id,
            'is_active' => true,
            'play_count' => 10,
        ]);

        Song::query()->create([
            'album_id' => $album->id,
            'artist_id' => $artist->id,
            'title' => 'Similar One',
            'artist' => 'Artist A',
            'duration' => 200,
            'track_number' => 2,
            'category_id' => $genre->id,
            'mood' => 'Energetic',
            'tag_id' => $tag->id,
            'is_active' => true,
            'play_count' => 50,
        ]);

        $similar = $this->getJson('/api/recommendations/similar/'.$seedSong->id.'?limit=10');
        $similar->assertOk()->assertJsonStructure(['data', 'meta']);

        $cold = $this->getJson('/api/recommendations/cold-start?limit=10');
        $cold->assertOk()->assertJsonStructure(['data', 'meta']);

        $track = $this->postJson('/api/recommendations/events', [
            'song_id' => $seedSong->id,
            'event_type' => 'recommendation_clicked',
            'recommendation_score' => 90,
            'recommendation_reason' => 'Because you frequently listen to Artist A',
        ]);

        $track->assertCreated();

        $this->assertDatabaseHas('recommendation_events', [
            'user_id' => $user->id,
            'song_id' => $seedSong->id,
            'event_type' => 'recommendation_clicked',
        ]);
    }
}
