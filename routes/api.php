<?php

use App\Http\Controllers\Api\BoxController;
use App\Http\Controllers\Api\ItemController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/box', [BoxController::class, 'index']);
    Route::post('/box', [BoxController::class, 'store']);
    Route::get('/box/{boxId}', [BoxController::class, 'show']);
    Route::patch('/box/{boxId}', [BoxController::class, 'update']);
    Route::delete('/box/{boxId}', [BoxController::class, 'destroy']);
    Route::patch('/box/{boxId}/restore', [BoxController::class, 'restore']);

    Route::get('/item', [ItemController::class, 'index']);
    Route::post('/item', [ItemController::class, 'store']);
    Route::get('/item/{itemId}', [ItemController::class, 'show']);
    Route::patch('/item/{itemId}', [ItemController::class, 'update']);
    Route::delete('/item/{itemId}', [ItemController::class, 'destroy']);
    Route::patch('/item/{itemId}/restore', [ItemController::class, 'restore']);
});
