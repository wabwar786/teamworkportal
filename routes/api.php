<?php

use App\Http\Controllers\Api\AgentApiController;
use App\Http\Controllers\Api\ChatApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Support chat API — public (used by widget.js and WinForms control)
|--------------------------------------------------------------------------
*/
Route::prefix('chat')->group(function () {
    Route::post('/open', [ChatApiController::class, 'open']);
    Route::post('/send', [ChatApiController::class, 'send']);
    Route::get('/poll', [ChatApiController::class, 'poll']);
    Route::post('/screenshot', [ChatApiController::class, 'screenshot']);
});

/*
|--------------------------------------------------------------------------
| Agent API — device monitoring
|--------------------------------------------------------------------------
*/
Route::prefix('agent')->group(function () {
    // enroll is open (uses employee slug); everything else needs the token
    Route::post('/enroll', [AgentApiController::class, 'enroll']);

    Route::middleware('agent')->group(function () {
        Route::post('/heartbeat', [AgentApiController::class, 'heartbeat']);
        Route::post('/events', [AgentApiController::class, 'events']);
        Route::post('/usage', [AgentApiController::class, 'usage']);
        Route::post('/screenshot', [AgentApiController::class, 'screenshot']);
        Route::get('/commands', [AgentApiController::class, 'pullCommands']);
        Route::post('/commands/ack', [AgentApiController::class, 'ackCommand']);
        Route::get('/blocklist', [AgentApiController::class, 'blocklist']);
    });
});
