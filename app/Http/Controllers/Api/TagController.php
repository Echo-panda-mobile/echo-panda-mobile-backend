<?php

namespace App\Http\Controllers\Api;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::orderBy('name')->get(['id', 'name', 'slug']);

        return response()->json($tags);
    }
}
