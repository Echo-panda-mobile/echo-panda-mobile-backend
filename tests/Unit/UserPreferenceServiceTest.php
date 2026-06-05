<?php

namespace Tests\Unit;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Song;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\UserPreferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserPreferenceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_favorite_updates_multi_dimension_preferences(): void
    {
        $user = User::factory()->create();
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
        ]);

        $service = new UserPreferenceService();
        $service->applyFavorite((int) $user->id, $song);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preference_type' => 'genre',
            'preference_value' => 'Pop',
            'preference_score' => 10,
        ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preference_type' => 'artist',
            'preference_value' => 'The Weeknd',
            'preference_score' => 10,
        ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preference_type' => 'mood',
            'preference_value' => 'Energetic',
            'preference_score' => 10,
        ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preference_type' => 'tag',
            'preference_value' => 'Night',
            'preference_score' => 10,
        ]);

        $this->assertEquals(4, UserPreference::query()->where('user_id', $user->id)->count());
    }
}
