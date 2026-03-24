<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Box;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Item::query()
            ->whereHas('box', fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest('id')
            ->get();

        return response()->json(ItemResource::collection($items));
    }

    public function show(Request $request, int $itemId): JsonResponse
    {
        $item = Item::query()
            ->whereHas('box', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($itemId);

        return response()->json(new ItemResource($item));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'box_id' => [
                'required',
                'integer',
                Rule::exists('boxes', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $box = Box::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($validated['box_id']);

        $item = $box->items()->create([
            'name' => $validated['name'],
        ]);

        return response()->json(new ItemResource($item), 201);
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $item = Item::query()
            ->whereHas('box', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($itemId);

        $item->update($validated);

        return response()->json(new ItemResource($item));
    }

    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $item = Item::query()
            ->whereHas('box', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($itemId);

        $item->delete();

        return response()->json(['message' => 'Item deleted.']);
    }

    public function restore(Request $request, int $itemId): JsonResponse
    {
        $item = Item::withTrashed()
            ->whereHas('box', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($itemId);

        if ($item->trashed()) {
            $item->restore();
        }

        return response()->json(new ItemResource($item));
    }
}
