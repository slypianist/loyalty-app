<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Models\Permission;

class RoleController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function index(Request $request){
        $roles = Role::orderBy('id', 'ASC')->get();
        return $this->sendResponse($roles, 'Successful');
   //     return view('roles.index', compact('roles'))->with('i', ($request->input('page', 1)-1)*5);
    }


    public function store(Request $request){
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required'

        ]);

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permissions'));
        return $this->sendResponse($role, 'Role created successfully');
      //  return redirect()->route('roles.index')->with('message', 'Role has been created');
    }

    public function show($id){
        try {
            $role = Role::findorFail();
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.id", "=", "permission_id")
                                       ->where("role_has_permissions.role_id", $id)
                                       ->get();
        $result['role'] = $role;
        $result['rolePermissions'] = $rolePermissions;
       // return view('roles.show', compact('role', 'rolePermissions'));
       return $this->sendResponse($result, 'Successful');
        } catch (ModelNotFoundException $th) {
            $th->getMessage();
            $this->sendError('Error fetching roles', $th->getMessage());

        }


    }

     public function editRoleDetails($id){
        try {
            $data['role'] = Role::find($id);
        $data['permissions'] = Permission::get();
        $data['rolePermissions'] = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
        ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')->all();

        return $this->sendResponse($data, 'Successful');
       // return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));

        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation Failed.', $th->getMessage());

        }

    }

    public function update(Request $request, Role $role){
        $request->validate([
            'name' => 'required',
            'permissions' => 'required',

        ]);

      //  dd($request->all());
        $role->syncPermissions($request->input('permissions'));

        return redirect()->route('roles.index')->with('message', 'role has been updated');
    }

    public function destroy($id){
        try {
         $role =   DB::table('roles')->where('id', $id)->delete();
            return $this->sendResponse($role, 'Deleted successfully');
        } catch (ModelNotFoundException $th) {
            $this->sendError('Operation failed.', $th->getMessage());
        }
       // return redirect()->route('roles.index')->with('message', 'Role has been deleted successfully');

    }

}
