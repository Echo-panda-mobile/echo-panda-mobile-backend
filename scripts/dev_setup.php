<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Artist;

$user = User::first();
if (! $user) {
    $user = User::factory()->create([
        'name' => 'Dev User',
        'email' => 'dev@example.local',
        'password' => bcrypt('password'),
    ]);
}

$user->update(['role' => 'artist']);

$artist = Artist::where('user_id', $user->id)->first();
if (! $artist) {
    $artist = Artist::create([
        'user_id' => $user->id,
        'name' => $user->name ?: 'Dev Artist',
        'slug' => 'dev-artist-' . $user->id,
    ]);
}

$token = $user->createToken('dev-cli')->plainTextToken;

echo json_encode(['token' => $token, 'user_id' => $user->id, 'artist_id' => $artist->id]);
