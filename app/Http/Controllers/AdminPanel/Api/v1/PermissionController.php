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

    public function updateRole (RoleRequest $request, $id) {
        return new RoleResource([Role::find($id)->update($request->all())]);
    }

    public function getPermissions () {
        return new RoleResource(Permission::all());
    }

    public function getPersonnel () {
        return new RoleResource(Admin::with('roles')->get());
    }

    public function createPersonnel(PersonnelRequest $request) {
        return new AdminResource(Admin::create($request->all())->assignRole(Role::find($request->role_id)));
    }

    public function updatePersonnel(PersonnelRequest $request, $id) {
        Admin::find($id)->update($request->all());
        $admin = Admin::find($id);
        $admin->removeRole($admin->getRoleNames()[0]);
        return new AdminResource([$admin->assignRole(Role::find($request->role_id))]);
    }

    // public function addRoleToPersonnel (RoleToPersonnelRequest $request) {
    //     return new AdminResource(Admin::find($request->admin_id)->assignRole(Role::find($request->role_id)));
    // }

    public function addPermissionToRole (Request $request, $id) {
        return new AdminResource(Role::find($id)->givePermissionTo($request->permission_name));
    }
}
