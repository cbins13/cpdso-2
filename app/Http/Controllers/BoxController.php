<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoxController extends Controller
{
    public function index(Request $request): Response
    {
        $boxes = $request->user()
            ->boxes()
            ->withCount('items')
            ->latest('id')
            ->get();

        return Inertia::render('boxes/Index', [
            'boxes' => $boxes,
        ]);
    }

    public function show(Request $request, int $boxId): Response
    {
        $box = $request->user()->boxes()->findOrFail($boxId);
        $items = $box->items()->latest('id')->get();

        return Inertia::render('boxes/Show', [
            'box' => $box,
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->boxes()->create($validated);

        return back();
    }

    public function update(Request $request, int $boxId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->boxes()->findOrFail($boxId)->update($validated);

        return back();
    }

    public function destroy(Request $request, int $boxId): RedirectResponse
    {
        $request->user()->boxes()->findOrFail($boxId)->delete();

        return back();
    }

    public function restore(Request $request, int $boxId): RedirectResponse
    {
        $box = $request->user()->boxes()->withTrashed()->findOrFail($boxId);

        if ($box->trashed()) {
            $box->restore();
        }

        return back();
    }

    public function storeItem(Request $request, int $boxId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->boxes()->findOrFail($boxId)->items()->create($validated);

        return back();
    }

    public function updateItem(Request $request, int $itemId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $item = Item::query()
            ->whereHas('box', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($itemId);

        $item->update($validated);

        return back();
    }

    public function destroyItem(Request $request, int $itemId): RedirectResponse
    {
        $item = Item::query()
            ->whereHas('box', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($itemId);

        $item->delete();

        return back();
    }

    public function restoreItem(Request $request, int $itemId): RedirectResponse
    {
        $item = Item::withTrashed()
            ->whereHas('box', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($itemId);

        if ($item->trashed()) {
            $item->restore();
        }

        return back();
    }
}
