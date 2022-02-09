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
        return new RoleResource(Role::with('permissions')->get());
    }

    public function createRole (RoleRequest $request) {
        return new RoleResource(Role::create($request->all()));
    }

    public function updateRole (Request $request, $id) {
        $role = Role::where('id', $id)->with('permissions')->first();
        $role->update($request->all());

        return new RoleResource($role);
    }

    public function getPermissions () {
        return new RoleResource(Permission::all());
    }

    public function getPersonnel () {
        return new RoleResource(Admin::with('roles.pernissions')->get());
    }

    public function createPersonnel(PersonnelRequest $request) {
        return new AdminResource(Admin::create($request->all())->assignRole(Role::find($request->role_id)));
    }

    public function updatePersonnel(Request $request, $id) {
        Admin::find($id)->update($request->all());
        $admin = Admin::find($id);
        $admin->removeRole($admin->getRoleNames()[0]);
        return new AdminResource([$admin->assignRole(Role::find($request->role_id))]);
    }

    // public function addRoleToPersonnel (RoleToPersonnelRequest $request) {
    //     return new AdminResource(Admin::find($request->admin_id)->assignRole(Role::find($request->role_id)));
    // }

    public function addPermissionToRole (Request $request, $id) {
        $role = Role::find($id);
        return $role->getPermissionNames();
        $role->revokePermissionTo($role->getPermissionNames());
        return $role;
        return new AdminResource(
                $role->givePermissionTo(Permission::whereIn('id', $request->permission_name)
                ->pluck('name')->toArray()));
    }

    public function deletePermissionFromRole (Request $request, $id) {
        $permission = Permission::whereIn('id', $request->permission_id)->pluck('name')->toArray();
        return new AdminResource(Role::find($id)->revokePermissionTo($permission));
    }

    public function deletePersonnel ($id) {
        return new AdminResource([Admin::find($id)->delete()]);
    }

    public function deleteRole ($id) {
        if (count(Role::find($id)->users)) {
            return response()->json([ 'errors' => ['The role cannot be deleted because it belongs to the user.']], 403);
        }
        return new AdminResource([Role::find($id)->delete()]);
    }
}
