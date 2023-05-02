<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class TestController extends BaseController
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

    public function index(){
        return $this->sendResponse('CD refactor is working fine.', true);

    }
}
