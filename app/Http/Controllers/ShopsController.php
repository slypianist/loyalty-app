<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShopsController extends BaseController
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

    public function createShop(Request $request){
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

    public function showShop($id){
        try {
            $shop = Shop::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred',$th->getMessage());
        }
        $data['shopDetails'] =  DB::table('shops')
                        ->join('users', 'shops.user_id', '=', 'users.id')
                        ->where('shops.id', '=', $shop->id)
                        ->select('shops.id AS shopID', 'shops.location AS shopLocation', 'shops.name AS shopName', 'shops.address AS shopAddress', 'shops.code AS shopCodeName',
                        'users.firstName AS partnerFirstName', 'users.lastName AS partnerLastName')
                        ->get();

        return $this->sendResponse($data,200);


    }

    //Update Shop
    public function updateShop($id){


    }

    public function destroyShop($id){
        try {
            $shop = Shop::findOrfail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        if($shop->status === 'ASSIGNED'){
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
