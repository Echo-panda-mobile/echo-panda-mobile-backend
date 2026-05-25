<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AlbumCoverUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_album_creation_accepts_a_cover_file_and_stores_a_public_url(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => User::ROLE_ARTIST]);
        $artist = Artist::create([
            'user_id' => $user->id,
            'name' => 'Echo Panda',
            'slug' => 'echo-panda',
            'bio' => 'Test artist',
            'cover_image_url' => null,
            'is_active' => true,
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->post('/api/albums', [
            'title' => 'Midnight Neon',
            'artist' => $artist->name,
            'description' => 'album',
            'release_status' => 'draft',
            'cover' => UploadedFile::fake()->image('cover.jpg', 1200, 1200)->size(1200),
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'title',
                'cover_url',
            ],
        ]);

        $albumId = $response->json('data.id');
        $album = Album::findOrFail($albumId);

        $this->assertNotNull($album->cover_url);
        $this->assertStringContainsString('/covers/', $album->cover_url);
        $this->assertSame($album->cover_url, $response->json('data.cover_url'));
    }

    public function test_album_update_accepts_a_new_cover_file_via_multipart_request(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => User::ROLE_ARTIST]);
        $artist = Artist::create([
            'user_id' => $user->id,
            'name' => 'Echo Panda',
            'slug' => 'echo-panda',
            'bio' => 'Test artist',
            'cover_image_url' => null,
            'is_active' => true,
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($user, ['*']);

        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Original Title',
            'artist' => $artist->name,
            'description' => 'album',
            'release_status' => 'draft',
        ]);

        $response = $this->post('/api/albums/'.$album->id, [
            '_method' => 'PUT',
            'title' => 'Original Title',
            'artist' => $artist->name,
            'description' => 'album',
            'release_status' => 'draft',
            'cover' => UploadedFile::fake()->image('updated-cover.jpg', 1200, 1200)->size(1200),
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.cover_url', $response->json('data.cover_url'));

        $album->refresh();

        $this->assertNotNull($album->cover_url);
        $this->assertStringContainsString('/covers/', $album->cover_url);
    }
}
