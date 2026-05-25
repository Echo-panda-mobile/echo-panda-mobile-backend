<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverUrlResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_cover_endpoints_return_absolute_urls_without_s3_signing(): void
    {
        $artist = Artist::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Echo Panda',
            'slug' => 'echo-panda',
            'image_url' => 'https://placehold.co/800x800/png?text=Echo+Panda',
            'is_active' => true,
            'verification_status' => 'pending',
        ]);

        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Midnight Neon',
            'slug' => 'midnight-neon',
            'artist' => $artist->name,
            'cover_key' => 'https://placehold.co/1200x1200/png?text=Midnight+Neon',
        ]);

        $song = Song::create([
            'album_id' => $album->id,
            'artist_id' => $artist->id,
            'title' => 'Neon River',
            'slug' => 'neon-river',
            'artist' => $artist->name,
            'duration' => 248,
            'file_size_bytes' => 9345021,
            'mime_type' => 'audio/mpeg',
            'track_number' => 1,
            'is_active' => true,
        ]);

        $albumResponse = $this->getJson('/api/albums/'.$album->id.'/cover-url');
        $albumResponse->assertOk();
        $albumResponse->assertJsonPath('signed_url', 'https://placehold.co/1200x1200/png?text=Midnight+Neon');

        $songResponse = $this->getJson('/api/songs/'.$song->id.'/cover-url');
        $songResponse->assertOk();
        $songResponse->assertJsonPath('signed_url', 'https://placehold.co/1200x1200/png?text=Midnight+Neon');

        $artistResponse = $this->getJson('/api/artists/'.$artist->id.'/image-url');
        $artistResponse->assertOk();
        $artistResponse->assertJsonPath('signed_url', 'https://placehold.co/800x800/png?text=Echo+Panda');
    }
}
