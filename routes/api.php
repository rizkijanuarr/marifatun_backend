<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\User\UserContentController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::middleware('role:'.RoleEnum::ADMIN->value)->group(function () {
            Route::apiResource('users', UserController::class);
            Route::get('dashboard/admin', [DashboardController::class, 'admin']);
            Route::apiResource('contents', ContentController::class);
        });

        Route::middleware('role:'.RoleEnum::MARIFATUN_USER->value)->group(function () {
            Route::get('dashboard/user', [DashboardController::class, 'user']);
            Route::prefix('user')->name('user.')->group(function () {
                Route::get('contents/statistics', [UserContentController::class, 'statistics'])
                    ->name('contents.statistics');
                Route::apiResource('contents', UserContentController::class);
            });
        });

    });

});
