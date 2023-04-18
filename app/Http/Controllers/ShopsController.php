<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // Save a shop

    public function store(Request $request){
        $this->validate($request, [
            'name' => 'required',
            'location' => 'required',
            'address' => 'required',
            'shopCode' => 'required'
        ],
        $messages=[

        ]);

        $data = $request->all();
        $shop = Shop::create($data);
        return response()->json(['message'=>'Created successfully'],200);

    }

    // View a shop

    public function show(Shop $shop){
        $id = $shop->id;
        $data['shopDetails'] =  DB::table('shops')
                        ->join('users', 'shops.user_id', '=', 'users.id')
                        ->where('shops.id', '=', $id)
                        ->select('shops.id AS shopID', 'shops.location AS shopLocation', 'shops.name AS shopName', 'shops.address AS shopAddress', 'shops.code AS shopCodeName',
                        'users.firstName AS partnerFirstName', 'users.lastName AS partnerLastName')
                        ->get();

        return response()->json(['result'=>$data], 200);


    }

    public function destroy(Shop $shop){
        if($shop->status == 'ASSIGNED'){
            return response()->json(['message'=>'Shop: '.$shop->name.' is currently assigned. Unassign before deleting.']);
        }else{
            // Delete shop
            $shop->delete();
            return response()->json(['Deleted successfully.']);
        }

    }

    /**
     * Assign a shop to a partner.
     *
     * @return \Illuminate\Http\JsonResponse
    */

    public function assignShop(Shop $shop, Request $request){
        $id = $request->id;

        $assignedPartner = User::where('id', $id)->first();

        $shop->partner_id = $assignedPartner->id;
        $shop->save();
        return response()->json(['message'=>$shop->name.' Assigned to '. $assignedPartner->lastName. $assignedPartner->firstName]);

       }

    /**
     * Unassign a shop to a partner.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unassignShop(Shop $shop){
        $id = $shop->partner_id;

        $assignedPartner = User::findOrFail($id)->get();
        $shop->partner_id = NULL;
        $shop->save();
        return response()->json(['message'=>$shop->name.' Unassigned from '. $assignedPartner->lastName]);

       }

}
