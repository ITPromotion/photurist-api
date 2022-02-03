<?php
use App\Http\Controllers\AdminPanel\Api\v1\ProfileController;
use App\Http\Controllers\AdminPanel\Api\v1\PermissionController;
use App\Http\Controllers\AdminPanel\Api\v1\UserController;
use App\Http\Controllers\AdminPanel\Api\v1\NotificationController;

Route::get('/get-profile', [ProfileController::class,'getProfile']);

Route::get('/get-role', [PermissionController::class, 'getRole']);

Route::post('/create-role', [PermissionController::class, 'createRole']);

Route::put('/update-role/{id}', [PermissionController::class, 'updateRole']);

Route::get('/get-permissions', [PermissionController::class, 'getPermissions']);

Route::get('/get-personnel', [PermissionController::class, 'getPersonnel']);

Route::post('/create-personnel', [PermissionController::class, 'createPersonnel']);

Route::put('/update-personnel/{id}', [PermissionController::class, 'updatePersonnel']);


Route::put('/add-role-to-personnel', [PermissionController::class, 'addRoleToPersonnel']);

Route::put('/add-permission-to-role/{id}', [PermissionController::class, 'addPermissionToRole']);

Route::get('/get-user', [UserController::class, 'getUser']);

Route::get('/get-info-user/{id}', [UserController::class, 'getInfoUser']);


Route::post('/send-push-user', [NotificationController::class, 'sendNotificationUser']);

Route::post('/create-group', [NotificationController::class, 'createGroup']);

Route::put('/update-group/{id}', [NotificationController::class, 'updateGroup']);

Route::delete('/delete-group/{id}', [NotificationController::class, 'deleteGroup'])
