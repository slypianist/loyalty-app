<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
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

    public function getPermission(){
        $permissions =    Permission::all()->pluck('name', 'id');
        return $this->sendResponse($permissions,true);


    }
}
