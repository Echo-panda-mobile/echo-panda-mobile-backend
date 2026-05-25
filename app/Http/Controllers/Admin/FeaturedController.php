<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeaturedController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', \App\Models\FeaturedItem::class);
        $items = FeaturedItem::latest()->paginate(20);

        return Inertia::render('Admin/Featured/Index', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\FeaturedItem::class);
        $request->validate(['item_type' => 'required', 'item_id' => 'required']);

        FeaturedItem::create($request->only(['item_type', 'item_id', 'priority']));

        return back()->with('success', 'Featured item added');
    }

    public function destroy(FeaturedItem $featuredItem)
    {
        $this->authorize('delete', $featuredItem);
        $featuredItem->delete();

        return back()->with('success', 'Featured item removed');
    }
}
