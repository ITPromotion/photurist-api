<?php
use App\Http\Controllers\AdminPanel\Api\v1\ProfileController;
use App\Http\Controllers\AdminPanel\Api\v1\PermissionController;
use App\Http\Controllers\AdminPanel\Api\v1\UserController;

Route::get('/get-profile', [ProfileController::class,'getProfile']);

Route::get('/get-role', [PermissionController::class, 'getRole']);

Route::post('/create-role', [PermissionController::class, 'createRole']);

Route::get('/get-permissions', [PermissionController::class, 'getPermissions']);

Route::get('/get-personnel', [PermissionController::class, 'getPersonnel']);

Route::post('/create-personnel', [PermissionController::class, 'createPersonnel']);

Route::put('/add-role-to-personnel', [PermissionController::class, 'addRoleToPersonnel']);

Route::put('/add-permission-to-role', [PermissionController::class, 'addPermissionToRole']);

Route::get('/get-user', UserController::class, 'getUser');

Route::get('/get-info-user', UserController::class, 'getInfoUser');

