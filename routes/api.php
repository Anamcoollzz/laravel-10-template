<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('hehe/{role}', [\App\Http\Controllers\RoleController::class, 'edit']);
Route::as('api.')->prefix('v1')->group(function () {
    # AUTH MODULES
    Route::post('auth/login', [AuthController::class, 'login'])->name('login');
    Route::post('auth/register', [AuthController::class, 'register'])->name('register');
    Route::post('auth/verify', [AuthController::class, 'verify'])->name('verify');
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/check-code', [AuthController::class, 'checkCode'])->name('check-code');
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');

    Route::middleware('auth:api')->group(function () {
        # PROFILES
        Route::get('profiles', [AuthController::class, 'profile'])->name('profiles');
        Route::post('profiles', [AuthController::class, 'updateProfile'])->name('profiles.update');
        Route::put('profiles/update-password', [AuthController::class, 'updatePassword'])->name('profiles.update-password');
        Route::get('profiles/log-activities', [AuthController::class, 'logActivities'])->name('profiles.log-activities');

        # SETTINGS
        Route::get('settings', [AuthController::class, 'settings'])->name('profiles.settings');

        # USERS
        Route::put('users/update-password/{user}', [UserManagementController::class, 'updatePassword'])->name('users.update-password');
        Route::apiResource('users', UserManagementController::class);

        # ROLES
        Route::get('permissions', [RoleController::class, 'permissions'])->name('permissions');
        Route::apiResource('roles', RoleController::class);
    });
});
