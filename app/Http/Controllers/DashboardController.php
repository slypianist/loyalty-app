<?php

namespace App\Http\Controllers;

use App\Models\Rep;
use App\Models\Shop;
use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
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


    public function partnerDashboard(){
        $id = auth()->user()->id;
      //  $data = [];
        $data['totalCustomersAward'] = DB::table('transactions')
                                    ->join('shops', 'shops.id', '=', 'transactions.shop_id')
                                    ->where('shops.id', '=', 1)
                                    ->count();
        $data['totalShops'] = Shop::where('user_id', $id)->count();
      //  $data['totalClaims'];
      //  $data['customers'];

      return $this->sendResponse($data, 'successful');

    }

}
