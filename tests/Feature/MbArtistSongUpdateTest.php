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

class MbArtistSongUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_can_update_song_metadata_via_mb_endpoint(): void
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
            'artist_id' => $artist->id,
            'title' => 'Old Title',
            'artist' => $artist->name,
            'duration' => 180,
            'track_number' => 1,
            'category_id' => $genre->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->putJson("/api/mb/artist/songs/{$song->id}", [
            'album_id' => $album->id,
            'title' => 'Updated Title',
            'duration' => 180,
            'track_number' => 1,
            'lyrics' => 'New lyrics line',
            'category_id' => $genre->id,
            'tag_id' => $tag->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.lyrics', 'New lyrics line')
            ->assertJsonPath('data.tag_id', $tag->id);

        $this->assertSame('Updated Title', $song->fresh()->title);
    }

    public function test_artist_can_edit_song_linked_via_album_when_song_artist_id_is_missing(): void
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

        $this->getJson("/api/mb/artist/songs/{$song->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Legacy Song');

        $this->assertSame($artist->id, $song->fresh()->artist_id);
    }
}
