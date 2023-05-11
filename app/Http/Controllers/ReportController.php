<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use App\Models\Activity;

class ReportController extends BaseController
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

        public function getClaims(){
        $claims =    DB::table('withdrawals')
                ->join('customers', 'customers.id', '=', 'withdrawals.customer_id')
                ->join('reps', 'reps.id', '=', 'withdrawals.rep_id')
                ->join('shops', 'shops.id', '=', 'withdrawals.shop_id')
                ->select('withdrawals.id as id','withdrawals.pointsRedeemed AS pointsClaimed', 'customers.firstName AS customerFirstName', 'customers.lastname AS customerLastName',
                                'shops.name AS shop', 'shops.address AS address', 'reps.firstName AS repFirstName', 'reps.lastName AS repsLastName')
                ->orderByDesc('withdrawals.id')
                ->get();
        return $this->sendResponse($claims, 'All claims transactions');

    }

        public function getTransactions(){
        $transactions =  DB::table('transactions')
                        ->join('customers', 'transactions.customer_id', '=', 'customers.id')
                        ->join('reps', 'reps.id', '=', 'transactions.rep_id')
                        ->join('shops', 'shops.id', '=', 'transactions.shop_id')
                        ->select('transactions.id as id', 'transactions.amount AS amount', 'transactions.awardedPoints AS points', 'customers.firstName AS customerFirstName', 'customers.lastname AS customerLastName',
                                'shops.name AS shop', 'shops.address AS address', 'reps.firstName AS repFirstName', 'reps.lastName AS repsLastName', 'transactions.created_at AS transactionTime')
                        ->orderByDesc('transactions.id')
                        ->get();
        return $this->sendResponse($transactions, 'All awarded points transactions');

    }


        public function getActivities(){
            $activity = Activity::orderBy('id', 'DESC')->get();

            return $this->sendResponse($activity, 'Activities');

    }

}
