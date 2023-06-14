<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class SettingsController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:reset-app', ['only'=> ['resetSetting']]);
    }


    public function resetSetting(){
        Account::query()->update(['point'=> 0, 'visit'=> 0]);

        return $this->sendResponse('Reset successful.', 'successful');

    }



}
