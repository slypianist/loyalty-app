<?php

namespace App\Http\Controllers;

use App\Models\Rep;
use App\Models\Shop;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class AdminDashboardController extends BaseController
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

    // Card Stats
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

    // Bar Stats

    public function graphStats(){
     //   $year = request('year'); // Assuming the year is passed as a query parameter named "year"
        $result = [];
          // Assuming the year is passed as a query parameter named "year"
        $year = request('year');
        $result[] = $year;
        $totalPts = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, SUM(awardedPoints) AS total FROM transactions WHERE YEAR(created_at) = $year GROUP BY YEAR(created_at), MONTH(created_at)) AS transactions RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON transactions.month = months.month"))
            ->select(DB::raw("months.month AS month, COALESCE(transactions.total, 0) AS total"))
            ->pluck('total')
            ->toArray();

        $totalCustomer = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, COUNT(*) AS total FROM customers WHERE YEAR(created_at) = $year GROUP BY YEAR(created_at), MONTH(created_at)) AS customers RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON customers.month = months.month"))
            ->selectRaw("months.month AS month, COALESCE(customers.total, 0) AS total")
            ->pluck('total')
            ->toArray();

        $claimed = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, SUM(pointsRedeemed) AS total FROM withdrawals WHERE YEAR(created_at) = $year GROUP BY YEAR(created_at), MONTH(created_at)) AS withdrawals RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON withdrawals.month = months.month"))
            ->select(DB::raw("months.month AS month, COALESCE(withdrawals.total, 0) AS total"))
            ->pluck('total')
            ->toArray();

        $totalVisits = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, SUM(visit) AS total FROM accounts WHERE YEAR(created_at) = $year GROUP BY YEAR(created_at), MONTH(created_at)) AS accounts RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON accounts.month = months.month"))
            ->select(DB::raw("months.month AS month, COALESCE(accounts.total, 0) AS total"))
            ->pluck('total')
            ->toArray();


         $totalEnrolled = DB::table(DB::raw("(SELECT MONTH(created_at) AS month, COUNT(*) AS total FROM accounts WHERE YEAR(created_at) = $year GROUP BY YEAR(created_at), MONTH(created_at)) AS accounts RIGHT JOIN (SELECT 1 AS month UNION SELECT 2 AS month UNION SELECT 3 AS month UNION SELECT 4 AS month UNION SELECT 5 AS month UNION SELECT 6 AS month UNION SELECT 7 AS month UNION SELECT 8 AS month UNION SELECT 9 AS month UNION SELECT 10 AS month UNION SELECT 11 AS month UNION SELECT 12 AS month) AS months ON accounts.month = months.month"))
            ->select(DB::raw("months.month AS month, COALESCE(accounts.total, 0) AS total"))
            ->pluck('total')
            ->toArray();

        $result[] = $totalCustomer;
        $result[] = $totalEnrolled;
        $result[] = $totalPts;
        $result[] = $totalVisits;
        $result[] = $claimed;

        return $this->sendResponse($result,true);
    }

    // Top Five Customers Stats

    public function topAccruer(){
        $topAccruer = DB::table('customers')
        ->join('transactions', 'customers.id', '=', 'transactions.customer_id')
        ->select('customers.id AS id', 'customers.firstName AS firstName', 'customers.lastName AS lastName', DB::raw('SUM(transactions.awardedPoints) as points'))
        ->groupBy('customers.id', 'customers.firstName', 'customers.lastName')
        ->orderByDesc('points')
        ->take(5)
        ->get();

        return $this->sendResponse($topAccruer, 'successful');

    }

        public function topRedeemed(){
            $topRedeemed = DB::table('customers')
            ->join('withdrawals', 'customers.id', '=', 'withdrawals.customer_id')
            ->select('customers.id AS id', 'customers.firstName AS firstName', 'customers.lastName AS lastName', DB::raw('SUM(withdrawals.pointsRedeemed) as points'))
            ->groupBy('customers.id', 'customers.firstName', 'customers.lastName')
            ->orderByDesc('points')
            ->take(5)
            ->get();

            return $this->sendResponse($topRedeemed, 'successful');

    }

        public function topUnclaimed(){
            $topUnclaimed = DB::table('accounts')
            ->join('customers', 'customers.id', '=', 'accounts.customer_id')
            ->select('accounts.id AS id', 'customers.firstName AS firstName', 'customers.lastName AS lastName','accounts.point AS points',)
            ->orderByDesc('accounts.point')
            ->take(5)
            ->get();

            return $this->sendResponse($topUnclaimed, 'successful');

    }

        public function topVisit(){
            $topVisit = DB::table('accounts')
            ->join('customers', 'customers.id', '=', 'accounts.customer_id')
            ->select('accounts.id AS id', 'customers.firstName AS firstName', 'customers.lastName AS lastName','accounts.visit AS visits',)
            ->orderByDesc('accounts.visit')
            ->take(5)
            ->get();

            return $this->sendResponse($topVisit, 'successful');

    }

    //Top Centers Stats

    public function centerTopAccruer(){
        $centerTopAccruer = DB::table('shops')
        ->join('transactions', 'shops.id', '=', 'transactions.shop_id')
        ->select('shops.id AS id', 'shops.name AS name', DB::raw('SUM(transactions.amount) as amount'))
        ->groupBy('shops.id', 'shops.name')
        ->orderByDesc('amount')
        ->take(5)
        ->get();

        return $this->sendResponse($centerTopAccruer, 'successful');

    }

    public function centerTopVisit(){
        $centerTopVisit = DB::table('shops')
        ->join('transactions', 'shops.id', '=', 'transactions.shop_id')
        ->select('shops.id AS id', 'shops.name AS name', DB::raw('COUNT(*) as visit'))
        ->groupBy('shops.id', 'shops.name')
        ->orderByDesc('visit')
        ->take(5)
        ->get();

        return $this->sendResponse($centerTopVisit, 'successful');

    }

    public function centerTopClaim(){
        $centerTopClaim = DB::table('shops')
        ->join('withdrawals', 'shops.id', '=', 'withdrawals.shop_id')
        ->select('shops.id AS id', 'shops.name AS name', DB::raw('SUM(withdrawals.pointsRedeemed) as points'))
        ->groupBy('shops.id', 'shops.name')
        ->orderByDesc('points')
        ->take(5)
        ->get();

        return $this->sendResponse($centerTopClaim, 'Top five Claims');

    }

    public function centerTopEnrol(){
        $centerTopEnrol = DB::table('shops')
        ->join('transactions', 'shops.id', '=', 'transactions.shop_id')
        ->select('shops.id AS id', 'shops.name AS name', DB::raw('COUNT(DISTINCT transactions.customer_id) as customers'))
        ->groupBy('shops.id', 'shops.name')
        ->orderByDesc('customers')
        ->take(5)
        ->get();

        return $this->sendResponse($centerTopEnrol, 'Top five enrolment');

    }
}
