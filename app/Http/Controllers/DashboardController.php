<?php

namespace App\Http\Controllers;

use App\Models\Rep;
use App\Models\Shop;
use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Http\Request;
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

    public function getPartnerCenter(){
        $id = auth()->user()->id;
        $data['centers'] = DB::table('shops')->where('user_id', $id)
                        ->select('id','shopCode', 'name', 'address')
                        ->get();

        return $this->sendResponse($data, 'successful');

    }

    // General Stats.

    public function partnerCardStatsAll(){
        $id = auth()->user()->id;
        $center = DB::table('shops')->where('user_id', $id)->pluck('id')->toArray();

        $data['totalCustomer'] = DB::table('transactions')->where('shop_id', $center)->count();

        $data['totalClaimed'] = DB::table('withdrawals')->where('shop_id', $center)->sum('pointsRedeemed');

        $data['totalAccrued'] = DB::table('transactions')->where('shop_id', $center)->sum('awardedPoints');

        $data['totalUnclaimed'] = $data['totalAccrued'] - $data['totalClaimed'];

        return $this->sendResponse($data, 'successful');

    }

    public function partnerCardStats(Request $request){
        $id = $request->centerId;
        $center = DB::table('shops')->where('id', $id)->pluck('id')->toArray();
        //dd($center);
        $data['totalCustomer'] = DB::table('transactions')->where('shop_id', $center)->count();

        $data['totalClaimed'] = DB::table('withdrawals')->where('shop_id', $center)->sum('pointsRedeemed');

        $data['totalAccrued'] = DB::table('transactions')->where('shop_id', $center)->sum('awardedPoints');

        $data['totalUnclaimed'] = $data['totalAccrued'] - $data['totalClaimed'];

        return $this->sendResponse($data, 'successful');

    }

    public function partnerBarStats(){
         $id = request('centerId');
        $result = [];
          // Assuming the year is passed as a query parameter named "year"
        $year = request('year');;
        $result[] = $year;

        $totalCustomer = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, COUNT(DISTINCT customer_id) AS total FROM transactions WHERE YEAR(created_at) = $year AND shop_id = $id GROUP BY YEAR(created_at), MONTH(created_at)) AS customers RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON customers.month = months.month"))
            ->selectRaw("months.month AS month, COALESCE(customers.total, 0) AS total")
            ->pluck('total')
            ->toArray();

         $totalPts = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, SUM(awardedPoints) AS total FROM transactions WHERE YEAR(created_at) = $year AND shop_id = $id GROUP BY YEAR(created_at), MONTH(created_at)) AS transactions RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON transactions.month = months.month"))
            ->select(DB::raw("months.month AS month, COALESCE(transactions.total, 0) AS total"))
            ->pluck('total')
            ->toArray();

        $totalVisits = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, COUNT(*) AS total FROM transactions WHERE YEAR(created_at) = $year AND shop_id = $id GROUP BY YEAR(created_at), MONTH(created_at)) AS accounts RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON accounts.month = months.month"))
            ->select(DB::raw("months.month AS month, COALESCE(accounts.total, 0) AS total"))
            ->pluck('total')
            ->toArray();


        $claimed = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, SUM(pointsRedeemed) AS total FROM withdrawals WHERE YEAR(created_at) = $year AND shop_id = $id GROUP BY YEAR(created_at), MONTH(created_at)) AS withdrawals RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON withdrawals.month = months.month"))
            ->select(DB::raw("months.month AS month, COALESCE(withdrawals.total, 0) AS total"))
            ->pluck('total')
            ->toArray();


        $totalEnrolled = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, COUNT(*) AS total FROM withdrawals WHERE YEAR(created_at) = $year AND shop_id = $id GROUP BY YEAR(created_at), MONTH(created_at)) AS accounts RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON accounts.month = months.month"))
            ->select(DB::raw("months.month AS month, COALESCE(accounts.total, 0) AS total"))
            ->pluck('total')
            ->toArray();

        $result[] = $totalCustomer;
      //  $result[] = $totalEnrolled;
        $result[] = $totalPts;
        $result[] = $totalVisits;
        $result[] = $claimed;

        return $this->sendResponse($result,true);

    }

    public function sideBarStats1(){
        $id = request('centerId');
        $data['topAccruer'] = DB::table('customers')
        ->select('customers.id','customers.firstName', 'customers.lastName', DB::raw('SUM(transactions.awardedPoints) as totalPoints'))
        ->join('transactions', 'customers.id', '=', 'transactions.customer_id')
        ->where('transactions.shop_id', $id)
        ->groupBy('customers.id', 'customers.firstName', 'customers.lastName')
        ->orderBy('totalPoints', 'desc')
        ->limit(5)
        ->get();

        return $this->sendResponse($data, 'Top Accruer.');

    }

    public function sideBarStats2(){
        $id = request('centerId');
        $data['topClaim'] = DB::table('customers')
        ->select('customers.id','customers.firstName', 'customers.lastName', DB::raw('SUM(withdrawals.pointsRedeemed) as claim'))
        ->join('withdrawals', 'customers.id', '=', 'withdrawals.customer_id')
        ->where('withdrawals.shop_id', $id)
        ->groupBy('customers.id', 'customers.firstName', 'customers.lastName')
        ->orderBy('claim', 'desc')
        ->limit(5)
        ->get();

        return $this->sendResponse($data, 'Top Claims.');

    }

    public function sideBarStats3(){
        $id = request('centerId');
        $data['topVisit'] = DB::table('customers')
        ->select('customers.id','customers.firstName', 'customers.lastName', DB::raw('COUNT(*) as totalVisit'))
        ->join('transactions', 'customers.id', '=', 'transactions.customer_id')
        ->where('transactions.shop_id', $id)
        ->groupBy('customers.id', 'customers.firstName', 'customers.lastName')
        ->orderBy('totalVisit', 'desc')
        ->limit(5)
        ->get();

        return $this->sendResponse($data, 'Top Visits.');

    }


}
