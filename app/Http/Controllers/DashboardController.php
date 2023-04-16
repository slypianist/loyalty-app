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
        //
    }

    public function adminDashboard(){
        $data['adminDetails'] = auth('admin')->user();


    }

    public function userDashboard(){

    }
}
