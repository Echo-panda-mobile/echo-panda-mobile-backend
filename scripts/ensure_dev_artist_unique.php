<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Models\Artist;
use Illuminate\Support\Str;

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$slug = 'dev-artist';
$artists = Artist::where('slug', $slug)->get();

if ($artists->count() <= 1) {
    echo "OK: count={$artists->count()}\n";
    exit(0);
}

// Keep the earliest created, delete others
$keep = $artists->sortBy('created_at')->first();
$delete = $artists->where('id', '!=', $keep->id);
foreach ($delete as $a) {
    echo "Deleting artist id={$a->id}\n";
    $a->delete();
}

echo "Cleanup done.\n";
