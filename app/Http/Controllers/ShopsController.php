<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Str;
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
        $shops = Shop::orderBy('id', 'ASC')->paginate(10);
       // return $this->sendResponse($shops, 200);
        return response()->json(['shops'=>$shops]);
    }

    // Save a shop

    public function createShop(Request $request){
        $this->validate($request, [
            'name' => 'required',
            'location' => 'required',
            'address' => 'required',
            'shopCode' => 'required',
           // 'shopCode' => 'required'
        ],
        $messages=[

        ]);

        $data = $request->all();
       // $data['shopCode'] = "SH-". substr(md5(uniqid(rand(), true)),0,5);
        dd($data);
        $shop = Shop::create($data);
        return $this->sendResponse($shop, 'Shop created succesfully');
     //   return response()->json(['message'=>'Created successfully'],200);

    }

    // View a shop

    public function showShop($id){
        try {
            $shop = Shop::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
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
    public function updateShop(Request $request, $id){
        try {
            $shop = Shop::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        $this->validate($request, [
            'name' => 'required',
            'location' => 'required',
            'address' => 'required',
           // 'shopCode' => 'required'
        ],
        $messages=[

        ]);

        $data = $request->all();
      //  $data['shopCode'] = "SH-". substr(md5(uniqid(rand(), true)),0,5);
        //dd($data);
        $shop->update($data);
        return $this->sendResponse($shop, 'Shop updated succesfully');

    }

    public function destroyShop($id){
        try {
            $shop = Shop::findOrfail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        if($shop->status === 'ASSIGNED'){
            return $this->sendError(['result'=>$shop->name.' is currently assigned. Unassign before deleting.']);
           // return response()->json(['message'=>'Shop: '.$shop->name.' is currently assigned. Unassign before deleting.']);
        }else{
            // Delete shop
            $shop->delete();
            return $this->sendResponse($shop, 'Shop deleted successfully');

        }

    }

    /**
     * Assign a shop to a partner.
     *
     * @return \Illuminate\Http\JsonResponse
    */

    public function assignShop(Request $request){
        $partnerId = $request->partnerId;
        $shopId = $request->shopId;

        try {
           $partner = User::where('id', $partnerId)->firstOrFail();
           $shop = Shop::where('id', $shopId)->firstOrFail();
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());

        }

        $shop->user()->associate($partner);
        $shop->status = "ASSIGNED";
        $shop->save();

        return response()->json(['status'=>200,'message'=>'Shop: '.$shop->name.' is now assigned to '. $partner->lastName.' '. $partner->firstName]);

       }

    /**
     * Unassign a shop to a partner.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unassignShop(Request $request){
        $shopId = $request->id;

        try {
            $shop = Shop::findOrFail($shopId);

        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());

        }
        // Check shop status
        if($shop->status === 'ASSIGNED'){
            $partnerId = $shop->user_id;

            try {
                $partner = User::findorFail($partnerId);
            } catch (ModelNotFoundException $th) {
                return $this->sendError('An error occurred', $th->getMessage());

            }
            // Unassign shop.
            $shop->user()->dissociate($partner);
            $shop->status = "UNASSIGNED";
            $shop->save();
            return response()->json(['message'=>$shop->name.' Unassigned from '. $partner->lastName]);

        }

        return $this->sendError('Not allowed. Shop is not assigned to any partner');

       }

}
