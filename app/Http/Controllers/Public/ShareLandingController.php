<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ShareMetadataService;
use Illuminate\Http\Request;

class ShareLandingController extends Controller
{
    protected $metadataService;

    public function __construct(ShareMetadataService $metadataService)
    {
        $this->metadataService = $metadataService;
    }

    public function show(Request $request, string $type, string $id)
    {
        $meta = $this->metadataService->getMetadata($type, $id);

        return view('public.share-landing', compact('meta'));
    }
}
