<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Artist\UploadController;
use App\Models\User;
use App\Models\Artist;

$artist = Artist::first();
$user = $artist?->user ?? User::first();
if (! $user) {
    echo json_encode(['error' => 'no_user']);
    exit(1);
}

$request = Request::create('/api/upload/media/presign', 'POST', [
    'purpose' => 'album_cover',
    'filename' => 'test-upload.png',
    'content_type' => 'image/png',
    'size' => 68,
]);

// make sure controller sees the authenticated user
$request->setUserResolver(function () use ($user) { return $user; });

$controller = new UploadController();
$response = $controller->presignMedia($request);

// response is JsonResponse
echo $response->getContent();
