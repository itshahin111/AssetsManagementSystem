<?php

use App\Http\Controllers\Api\V1\AssetCategoryController;
use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\AssetTypeController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BuildingController;
use App\Http\Controllers\Api\V1\FloorController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\RoomTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::apiResource('buildings', BuildingController::class);
        Route::patch('buildings/{building}/reorder', [BuildingController::class, 'reorder']);
        Route::patch('buildings/{building}/status', [BuildingController::class, 'updateStatus']);

        Route::apiResource('floors', FloorController::class);
        Route::patch('floors/{floor}/reorder', [FloorController::class, 'reorder']);

        Route::apiResource('room-types', RoomTypeController::class)->except(['show']);
        Route::apiResource('rooms', RoomController::class);

        Route::apiResource('asset-categories', AssetCategoryController::class);
        Route::apiResource('asset-types', AssetTypeController::class);
        Route::apiResource('assets', AssetController::class);
    });
});
