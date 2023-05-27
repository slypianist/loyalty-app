<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class RepDashboardController extends BaseController
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

    public function cardStats(Request $request){
         $id = auth('rep')->user()->id;
         $centerId = $request->centerId;

        $data['customerVisits']= DB::table('transactions')
        ->where('transactions.shop_id', $centerId)
        ->RightJoin('shops','shops.id', '=', 'transactions.shop_id')
        ->whereNotNull(['shops.id', 'transactions.shop_id'])
        ->count();
       $data['totalpurchases'] = DB::table('transactions')->where('transactions.shop_id', $centerId)->sum('amount');
        $data['totalPoints'] = DB::table('transactions')->where('transactions.shop_id', $centerId)
        ->sum('awardedPoints');
        $data['totalACP'] = DB::table('withdrawals')->where('withdrawals.shop_id', $centerId)
        ->sum('pointsRedeemed');

        return $this->sendResponse($data, 'successful');
    }
}
