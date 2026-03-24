<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoxResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $boxes = $request->user()
            ->boxes()
            ->with('items')
            ->latest('id')
            ->get();

        return response()->json(BoxResource::collection($boxes));
    }

    public function show(Request $request, int $boxId): JsonResponse
    {
        $box = $request->user()
            ->boxes()
            ->with('items')
            ->findOrFail($boxId);

        return response()->json(new BoxResource($box));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $box = $request->user()->boxes()->create($validated);
        $box->load('items');

        return response()->json(new BoxResource($box), 201);
    }

    public function update(Request $request, int $boxId): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $box = $request->user()->boxes()->findOrFail($boxId);
        $box->update($validated);
        $box->load('items');

        return response()->json(new BoxResource($box));
    }

    public function destroy(Request $request, int $boxId): JsonResponse
    {
        $box = $request->user()->boxes()->findOrFail($boxId);
        $box->delete();

        return response()->json(['message' => 'Box deleted.']);
    }

    public function restore(Request $request, int $boxId): JsonResponse
    {
        $box = $request->user()->boxes()->withTrashed()->findOrFail($boxId);

        if ($box->trashed()) {
            $box->restore();
        }

        $box->load('items');

        return response()->json(new BoxResource($box));
    }
}
