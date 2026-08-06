<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BuildingCommandController;
use App\Http\Controllers\Api\V1\GameConfigController;
use App\Http\Controllers\Api\V1\ResidentCommandController;
use App\Http\Controllers\Api\V1\WorldController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/game-config', [GameConfigController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        Route::get('/worlds', [WorldController::class, 'index']);
        Route::post('/worlds', [WorldController::class, 'store']);
        Route::get('/worlds/{world}', [WorldController::class, 'show']);
        Route::get('/worlds/{world}/state', [WorldController::class, 'state']);
        Route::get('/worlds/{world}/changes', [WorldController::class, 'changes']);
        Route::post('/worlds/{world}/buildings', [BuildingCommandController::class, 'place']);
        Route::patch('/worlds/{world}/buildings/{building}/move', [BuildingCommandController::class, 'move']);
        Route::delete('/worlds/{world}/buildings/{building}', [BuildingCommandController::class, 'remove']);
        Route::post('/worlds/{world}/buildings/{building}/collect', [BuildingCommandController::class, 'collect']);
        Route::post('/worlds/{world}/residents', [ResidentCommandController::class, 'recruit']);
        Route::post('/worlds/{world}/residents/{resident}/assign', [ResidentCommandController::class, 'assign']);
        Route::post('/worlds/{world}/residents/{resident}/unassign', [ResidentCommandController::class, 'unassign']);
    });
});
