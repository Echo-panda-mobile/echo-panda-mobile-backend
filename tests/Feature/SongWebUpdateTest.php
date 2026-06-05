<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Song;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SongWebUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_can_update_legacy_song_via_web_put_and_tag_id_is_saved(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ARTIST]);
        $artist = Artist::create([
            'user_id' => $user->id,
            'name' => 'Echo Panda',
            'slug' => 'echo-panda',
            'is_active' => true,
            'verification_status' => 'pending',
        ]);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Test Album',
            'artist' => $artist->name,
            'release_status' => 'draft',
        ]);
        $genre = Genre::create(['name' => 'Pop', 'slug' => 'pop', 'is_active' => true]);
        $tag = Tag::create(['name' => 'Chill', 'slug' => 'chill', 'is_active' => true]);
        $song = Song::create([
            'album_id' => $album->id,
            'artist_id' => null,
            'title' => 'Legacy Song',
            'artist' => $artist->name,
            'duration' => 180,
            'track_number' => 1,
            'category_id' => $genre->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->putJson("/api/songs/{$song->id}", [
            'album_id' => $album->id,
            'title' => 'Updated Legacy Song',
            'duration' => 180,
            'track_number' => 1,
            'lyrics' => 'Updated lyrics',
            'category_id' => $genre->id,
            'tag_id' => $tag->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Legacy Song')
            ->assertJsonPath('data.tag_id', $tag->id);

        $fresh = $song->fresh();
        $this->assertSame($artist->id, $fresh->artist_id);
        $this->assertSame($tag->id, $fresh->tag_id);
    }

    public function test_artist_can_update_song_when_album_belongs_to_them_but_song_artist_id_is_catalog(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ARTIST]);
        $artist = Artist::create([
            'user_id' => $user->id,
            'name' => 'My Stage Name',
            'slug' => 'my-stage-name',
            'is_active' => true,
            'verification_status' => 'pending',
        ]);
        $catalogArtist = Artist::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Billie Eilish',
            'slug' => 'billie-eilish',
            'is_active' => true,
            'verification_status' => 'verified',
        ]);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'My Album',
            'artist' => $artist->name,
            'release_status' => 'published',
        ]);
        $genre = Genre::create(['name' => 'Pop', 'slug' => 'pop', 'is_active' => true]);
        $song = Song::create([
            'album_id' => $album->id,
            'artist_id' => $catalogArtist->id,
            'title' => 'WILDFLOWER',
            'artist' => 'Billie Eilish',
            'duration' => 180,
            'track_number' => 1,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->putJson("/api/songs/{$song->id}", [
            'album_id' => $album->id,
            'title' => 'WILDFLOWER',
            'duration' => 180,
            'track_number' => 1,
            'category_id' => $genre->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'WILDFLOWER')
            ->assertJsonPath('data.category_id', $genre->id);

        $fresh = $song->fresh();
        $this->assertSame($artist->id, $fresh->artist_id);
        $this->assertSame('My Stage Name', $fresh->artist);
    }
}
