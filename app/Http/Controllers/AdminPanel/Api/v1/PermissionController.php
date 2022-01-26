<?php

namespace App\Http\Controllers\AdminPanel\Api\v1;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\RoleResource;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use App\Http\Requests\Admin\RoleRequest;
use App\Http\Requests\Admin\PersonnelRequest;
use App\Http\Requests\Admin\RoleToPersonnelRequest;

class PermissionController extends Controller
{
    public function getRole () {
        return new RoleResource(Role::all());
    }

    public function createRole (RoleRequest $request) {
        return new RoleResource(Role::create($request->all()));
    }

    public function getPermissions () {
        return new RoleResource(Permission::all());
    }

    public function getPersonnel () {
        return new RoleResource(Admin::with('roles')->get());
    }

    public function createPersonnel(PersonnelRequest $request) {
        return new AdminResource(Admin::create($request->all()));
    }

    public function addRoleToPersonnel (RoleToPersonnelRequest $request) {
        return new AdminResource(Admin::find($request->admin_id)->assignRole(Role::find($request->role_id)));
    }

    public function addPermissionToRole (RoleToPersonnelRequest $request) {
        return new AdminResource(Role::find($request->role_id)->givePermissionTo(Permission::find($request->permission_id)));
    }
}
