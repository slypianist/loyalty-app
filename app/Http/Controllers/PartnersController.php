<?php

namespace App\Http\Controllers;

use App\Models\Partner;

class PartnersController extends Controller
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
        $partners = Partner::all()->paginate(10);
        return response()->json(['partners'=>$partners]);

    }

    public function store(){

    }

    public function update(){

    }

    public function destroy(){

    }
}
