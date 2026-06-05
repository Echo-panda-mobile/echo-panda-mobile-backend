<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArtistProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_can_update_bio_via_profile_endpoint(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ARTIST]);
        $artist = Artist::create([
            'user_id' => $user->id,
            'name' => 'Echo Panda',
            'slug' => 'echo-panda',
            'bio' => 'Old bio',
            'is_active' => true,
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson('/api/artist/profile', [
            'id' => $artist->id,
            'name' => $artist->name,
            'bio' => 'Updated artist biography',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.bio', 'Updated artist biography');

        $this->assertSame('Updated artist biography', $artist->fresh()->bio);
    }

    public function test_non_artist_cannot_update_artist_profile(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user, ['*']);

        $this->putJson('/api/artist/profile', [
            'name' => 'Hacker',
            'bio' => 'Nope',
        ])->assertNotFound();
    }
}
