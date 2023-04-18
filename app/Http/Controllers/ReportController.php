<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
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
        DB::table('withdrawals')
        ->join('customers', 'customers.id', '=', 'withdrawals.customer_id')
        ->join('','')
        ->select('');


    }


}
