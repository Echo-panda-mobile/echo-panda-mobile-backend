<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Artist 1
        $artist1 = Artist::firstOrCreate([
            'name' => 'The Echoes',
            'slug' => 'the-echoes'
        ]);

        $album1 = Album::firstOrCreate([
            'artist_id' => $artist1->id,
            'title' => 'Reflections',
            'slug' => 'reflections',
            'artist' => $artist1->name,
        ]);

        Song::firstOrCreate([
            'album_id' => $album1->id,
            'title' => 'Mirror Lake',
            'artist' => $artist1->name,
            'track_number' => 1,
            'duration' => 200,
            'mime_type' => 'audio/mpeg',
        ]);

        Song::firstOrCreate([
            'album_id' => $album1->id,
            'title' => 'Sunset Echo',
            'artist' => $artist1->name,
            'track_number' => 2,
            'duration' => 185,
            'mime_type' => 'audio/mpeg',
        ]);

        // Artist 2
        $artist2 = Artist::firstOrCreate([
            'name' => 'Neon Panda',
            'slug' => 'neon-panda'
        ]);

        $album2 = Album::firstOrCreate([
            'artist_id' => $artist2->id,
            'title' => 'Night Drive',
            'slug' => 'night-drive',
            'artist' => $artist2->name,
        ]);

        Song::firstOrCreate([
            'album_id' => $album2->id,
            'title' => 'City Lights',
            'artist' => $artist2->name,
            'track_number' => 1,
            'duration' => 210,
            'mime_type' => 'audio/mpeg',
        ]);

        Song::firstOrCreate([
            'album_id' => $album2->id,
            'title' => 'Midnight Run',
            'artist' => $artist2->name,
            'track_number' => 2,
            'duration' => 195,
            'mime_type' => 'audio/mpeg',
        ]);
    }
}
