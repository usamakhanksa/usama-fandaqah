
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\TeamController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\PermissionController;
use App\Http\Controllers\Settings\SettingController;

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    Route::resource('teams', TeamController::class)->names('teams');
    Route::resource('users', UserController::class)->names('users');
    Route::resource('roles', RoleController::class)->names('roles');
    Route::resource('permissions', PermissionController::class)->names('permissions');
    Route::resource('settings', SettingController::class)->names('settings');
});

