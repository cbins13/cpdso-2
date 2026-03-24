<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display report of items grouped by box and owner.
     */
    public function itemsByBoxAndOwner(): Response
    {
        $data = DB::table('users')
            ->join('boxes', 'boxes.user_id', '=', 'users.id')
            ->leftJoin('items', function ($join) {
                $join->on('items.box_id', '=', 'boxes.id')
                    ->whereNull('items.deleted_at');
            })
            ->whereNull('boxes.deleted_at')
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'boxes.id as box_id',
                'boxes.name as box_name',
                DB::raw('COUNT(items.id) as item_count')
            )
            ->groupBy('boxes.id', 'boxes.user_id', 'users.id', 'users.name', 'boxes.name')
            ->orderBy('users.name')
            ->orderBy('boxes.name')
            ->get();

        return Inertia::render('reports/ItemsByBox', [
            'reportData' => $data,
        ]);
    }

    /**
     * Display report of most common items across all boxes.
     */
    public function mostCommonItems(): Response
    {
        $data = DB::table('items')
            ->whereNull('items.deleted_at')
            ->select(
                DB::raw('LOWER(name) as item_name'),
                DB::raw('COUNT(*) as frequency')
            )
            ->groupBy(DB::raw('LOWER(name)'))
            ->orderByDesc('frequency')
            ->orderBy(DB::raw('LOWER(name)'))
            ->limit(50)
            ->get();

        return Inertia::render('reports/MostCommonItems', [
            'reportData' => $data,
        ]);
    }
}
