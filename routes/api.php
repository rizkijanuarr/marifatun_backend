<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\TopupRequestController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserCreditController;
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
            Route::apiResource('user-credits', UserCreditController::class)
                ->except(['index'])
                ->parameters(['user-credits' => 'userCredit']);
            Route::get('dashboard/admin', [DashboardController::class, 'admin']);
        });

        Route::middleware('role:'.RoleEnum::MARIFATUN_USER->value)->group(function () {
            Route::get('dashboard/user', [DashboardController::class, 'user']);
        });

        Route::middleware('role:'.RoleEnum::ADMIN->value.'|'.RoleEnum::MARIFATUN_USER->value)->group(function () {
            Route::apiResource('contents', ContentController::class);

            Route::get('user-credits', [UserCreditController::class, 'index']);

            Route::get('topup-requests', [TopupRequestController::class, 'index']);
            Route::post('topup-requests', [TopupRequestController::class, 'store']);
            Route::get('topup-requests/{topupRequest}', [TopupRequestController::class, 'show']);
        });

        Route::middleware('role:'.RoleEnum::ADMIN->value)->group(function () {
            Route::put('topup-requests/{topupRequest}', [TopupRequestController::class, 'update']);
            Route::patch('topup-requests/{topupRequest}', [TopupRequestController::class, 'update']);
            Route::delete('topup-requests/{topupRequest}', [TopupRequestController::class, 'destroy']);
        });

    });

});
