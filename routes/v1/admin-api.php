<?php
use App\Http\Controllers\AdminPanel\Api\v1\ProfileController;
use App\Http\Controllers\AdminPanel\Api\v1\PermissionController;
use App\Http\Controllers\AdminPanel\Api\v1\UserController;
use App\Http\Controllers\AdminPanel\Api\v1\NotificationController;

Route::group(['middleware' => ['role_or_permission:Super Admin|Roles']], function () {

    Route::get('/get-profile', [ProfileController::class,'getProfile']);

    Route::get('/get-role', [PermissionController::class, 'getRole']);

    Route::post('/create-role', [PermissionController::class, 'createRole']);

    Route::put('/update-role/{id}', [PermissionController::class, 'updateRole']);

    Route::delete('/delete-role/{id}', [PermissionController::class, 'deleteRole']);

    Route::get('/get-permissions', [PermissionController::class, 'getPermissions']);

    Route::put('/add-permission-to-role/{id}', [PermissionController::class, 'addPermissionToRole']);

    Route::put('/delete-permission-from-role/{id}', [PermissionController::class, 'deletePermissionFromRole']);


});
Route::group(['middleware' => ['role_or_permission:Super Admin|Administrators']], function () {
    Route::get('/get-personnel', [PermissionController::class, 'getPersonnel']);

    Route::post('/create-personnel', [PermissionController::class, 'createPersonnel']);

    Route::put('/update-personnel/{id}', [PermissionController::class, 'updatePersonnel']);

    Route::delete('/delete-personnel/{id}', [PermissionController::class, 'deletePersonnel']);

    Route::put('/add-role-to-personnel', [PermissionController::class, 'addRoleToPersonnel']);

});

Route::group(['middleware' => ['role_or_permission:Super Admin|Users']], function () {
    Route::get('/get-users', [UserController::class, 'getUsers']);

    Route::get('/get-info-user/{id}', [UserController::class, 'getInfoUser']);

    Route::put('/update-user-status/{id}', [UserController::class, 'updateStatusUser']);
});

Route::group(['middleware' => ['role_or_permission:Super Admin|Notifications']], function () {
    Route::post('/send-push-user', [NotificationController::class, 'sendNotificationUser']);

});







// Route::post('/create-group', [NotificationController::class, 'createGroup']);

// Route::put('/update-group/{id}', [NotificationController::class, 'updateGroup']);

// Route::delete('/delete-group/{id}', [NotificationController::class, 'deleteGroup']);

// Route::get('/get-all-groups', [NotificationController::class, 'getAllGroup']);
