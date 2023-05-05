<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function totalModels(){

        $data['totalCustomer'] = 0;
        $data['totalCenter'] = 0;
        $data['totalPartner'] = 0;
        $data['customers']  = 0;


    }


    public function repDashboard(){
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
