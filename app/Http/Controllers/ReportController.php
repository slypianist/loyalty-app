<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

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
            $activity = Activity::orderBy('id', 'DESC')->take(12)->get();
            return $this->sendResponse($activity, 'Activities');
    }

    public function getPartnerClaimReport(){
        $id = auth()->user()->id;
        $center = DB::table('shops')->where('user_id', $id)->pluck('id')->toArray();
        $data['claim'] = DB::table('withdrawals')
        ->join('customers', 'customers.id', '=', 'withdrawals.customer_id')
        ->join('shops', 'shops.id', '=', 'withdrawals.shop_id')
        ->where('shop_id', $center)
        ->select('withdrawals.id', 'shops.name', 'withdrawals.pointsRedeemed', 'customers.firstName AS firstName', 'customers.lastName AS lastName', 'withdrawals.pointsRedeemed', 'withdrawals.created_at')
        ->orderByDesc('withdrawals.id')
        ->get();
        return $this->sendResponse($data, 'successful');
    }

    public function getPartnerPointReport(){
        $id = auth()->user()->id;
        $center = DB::table('shops')->where('user_id', $id)->pluck('id')->toArray();
        $data['claim'] = DB::table('transactions')
        ->join('customers', 'customers.id', '=', 'transactions.customer_id')
        ->join('shops', 'shops.id', '=', 'transactions.shop_id')
        ->where('shop_id', $center)
        ->select('transactions.id', 'shops.name', 'transactions.awardedPoints', 'customers.firstName AS firstName', 'customers.lastName AS lastName', 'transactions.awardedPoints', 'transactions.created_at')
        ->orderByDesc('transactions.id')
        ->get();
        return $this->sendResponse($data, 'successful');

    }

}
