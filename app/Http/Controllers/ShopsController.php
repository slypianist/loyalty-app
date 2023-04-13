<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Partner;
use Illuminate\Http\Request;

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

    public function store(Request $request){
        $this->validate($request, [
            'name' => 'required',
            'location' => 'required',
            'address' => 'required',
            'shopeCode' => 'required'
        ],
        $messages=[

        ]);

        $data = $request->all();
        $shop = Shop::create($data);
        return response()->json(['message'=>'Created successfully'],200);

    }

    public function show(Shop $shop){

    }

    public function destroy(Shop $shop){

    }

    /**
     * Assign a shop to a partner.
     *
     * @return \Illuminate\Http\JsonResponse
    */

    public function assignShop(Shop $shop, $pid){
        $assignPartner = Partner::findOrFail($pid)->get();
        $shop->partner_id = $assignPartner->id;
        return response()->json(['message'=>$shop->name.' Assigned to '. $assignPartner->lastName]);

       }

    /**
     * Unassign a shop to a partner.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unassignShop(Shop $shop, $pid){
        $assignPartner = Partner::findOrFail($pid)->get();
        $shop->partner_id = NULL;
        return response()->json(['message'=>$shop->name.' Unassigned from '. $assignPartner->lastName]);

       }
}
