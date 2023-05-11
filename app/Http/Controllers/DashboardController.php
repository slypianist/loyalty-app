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

    public function cardStats(){

        $totalAP = DB::table('transactions')->sum('awardedPoints');
        $totalClaim = DB::table('withdrawals')->sum('pointsRedeemed');
        $data['totalCustomer']  =  Customer::count();
        $data['totalCenter'] = Shop::count();
        $data['totalPartner'] = User::count();
        $data['totalRep'] = Rep::count();
        $data['totalVisits']  = DB::table('accounts')->sum('visit');
        $data['totalAP'] = $totalAP;
        $data['totalClaimed'] = $totalClaim;
        $data['totalUnclaimed'] =  $totalAP - $totalClaim;


        return $this->sendResponse($data, true);

    }

    public function graphStats(){
     //   $year = request('year'); // Assuming the year is passed as a query parameter named "year"

     $data['year'] = request('year'); // Assuming the year is passed as a query parameter named "year"
        $year = $data['year'];
        $data['allCustomers'] = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, COUNT(*) AS total FROM customers WHERE YEAR(created_at) = $year GROUP BY YEAR(created_at), MONTH(created_at)) AS customers RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON customers.month = months.month"))
            ->select(DB::raw("months.month AS month, IFNULL(customers.total, 0) AS total"))
            ->get();
        // = $year;


return $this->sendResponse($data,true);


   // dd($customers);

       /*  $totalAccrued=0;
        $totalRedeemed=0;
        $totalVisit=0;

         */



    }


    public function repCardStats(){
        $id = auth('rep')->user()->id;

        $data['totalCustomer']=0;
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
