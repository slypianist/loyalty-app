<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\BaseController;
use Spatie\Permission\Models\Permission;

class DashboardController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function cardStats(){
        $data['totalCustomer']  =  Customer::count();
        $data['totalCenter'] = Shop::count();
        $data['totalPartner'] = User::count();
        $data['totalAdmin'] = Admin::count();
        $data['totalRoles'] = Role::count();
        $data['totalPermissions'] = Permission::count();

        return $this->sendResponse($data, true);

    }

    public function graphStats(){


    }


    public function repCardStats(){
        $id = auth('rep')->user()->id;

        $data['totalCustomer'] = Cust
        $data['adminDetails'] = auth('rep')->user();
        $data = [];
        $data['totalCustomers'];
        $data['totalShops'];
        $data['totalClaims'];
        $data['customers'];

    }

    public function partnerDashboard(){
        $data['adminDetails'] = auth()->user();
        $data = [];
        $data['totalCustomers'];
        $data['totalShops'];
        $data['totalClaims'];
        $data['customers'];

    }

}
