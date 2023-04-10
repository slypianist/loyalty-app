<?php

namespace App\Http\Controllers;

use App\Models\Shop;

class ShopsController extends Controller
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
        $shops = Shop::all()->orderBy('id')->paginate(10);
        return response()->json(['shops'=>$shops]);
    }

    public function store(){

    }

    public function show(Shop $shop){

    }

    public function destroy(Shop $shop){

    }
}
