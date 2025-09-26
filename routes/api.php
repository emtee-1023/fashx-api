<?php

use App\Http\Controllers\AiJobController;
use App\Http\Controllers\MockupController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SketchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::apiResource('projects', controller: ProjectController::class)->only(['index', 'store', 'show']);
        Route::get('projects/{project}/sketches', [SketchController::class, 'index']);
        Route::post('projects/{project}/sketches', [SketchController::class, 'store']);
        Route::apiResource('mockups', MockupController::class);
        Route::apiResource('patterns', PatternController::class);
        Route::apiResource('ai-jobs', AiJobController::class);
    });
