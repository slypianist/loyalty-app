<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\BaseController;
use ErrorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
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
        $this->middleware('permission:list-roles|create-role|update-role|delete-role|view-role', ['only'=> ['index']]);
        $this->middleware('permission:create-role', ['only'=> ['store']]);
        $this->middleware('permission:view-role', ['only'=> ['show']]);
        $this->middleware('permission:update-role', ['only'=> ['update']]);
        $this->middleware('permission:delete-role', ['only'=> ['destroy']]);
    }
    public function index(){
        $roles = Role::all();
        if(count($roles) > 0){
            return $this->sendResponse($roles, true);

        }
        else{
            return $this->sendResponse(NULL, 'No role(s) record found or created.');
        }

    }


    public function store(Request $request){
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permissions' => 'required'

        ]);
       // dd($request->all());

        try {
            $role = Role::create(['guard_name'=>'admin','name' => $request->input('name')]);
            $role->syncPermissions($request->input('permissions'));
        } catch (PermissionDoesNotExist $th) {
            return $this->sendError('Permission does not exit', $th->getMessage());
        }

        return $this->sendResponse($role, 'Role created successfully');
    }

    public function show($id){
        try {
            $role = Role::findorFail($id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id", "=", "permissions.id")
                                       ->where("role_has_permissions.role_id", $id)
                                       ->get();
        $result['role'] = $role;
        $result['rolePermissions'] = $rolePermissions;
       // return view('roles.show', compact('role', 'rolePermissions'));
       return $this->sendResponse($result, true);
        } catch (ModelNotFoundException $th) {
            $th->getMessage();
            $this->sendError('Error fetching roles', $th->getMessage());

        }

    }

      public function rolePermissions($id){
        try {
            $data['role'] = Role::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation Failed.', $th->getMessage());
        }
        $data['permissions'] = Permission::get();
        $data['rolePermissions'] = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
                                ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                                ->pluck('permissions.name AS permission', 'permissions.id AS id',)
                                ->all();

        return $this->sendResponse($data, 'Successful');

    }

    public function update(Request $request, $id){
        $this->validate($request,[
            'name' => 'required',
            'permissions' => 'required',

        ]);

        try {
            $role = Role::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('No record for this query', $th->getMessage());
        }
            $role->name = $request->name;
            $role->update();
        try {
            $role->syncPermissions($request->input('permissions'));
        } catch (PermissionDoesNotExist $th) {
            return $this->sendError('Just dey play. Permission does not exist', $th->getMessage());
        }

        return $this->sendResponse($role, 'Role updated successfully');

    }

    public function destroy($id){

        $role =   Role::where('id', $id)->first();

        try {
            if($role->name == "Super Admin"){
                return $this->sendError('Action failed. Cannot delete Super Admin Role');
             }else{
                $role->delete();
                return $this->sendResponse($role, 'Role deleted successfully');
             }

        } catch (ErrorException $th) {
           return $this->sendError('Invalid Role ID given');
        }

    }

}
