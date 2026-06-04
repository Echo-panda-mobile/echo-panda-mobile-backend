<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Pop',
            'Hip Hop',
            'R&B',
            'Rock',
            'Electronic',
            'Jazz',
            'Classical',
            'K-Pop',
            'Lo-Fi',
            'Country',
            'Latin',
            'Blues',
            'Metal',
            'Reggae',
            'Folk',
        ];

        foreach ($genres as $name) {
            Genre::updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }
    }
}
