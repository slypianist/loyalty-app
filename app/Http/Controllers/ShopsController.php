<?php

namespace App\Http\Controllers;

use App\Models\Rep;
use App\Models\Shop;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $this->middleware('permission:list-centers|create-center|view-center|update-center|delete-center|assign-center-partner|unassign-center-partner|assign-center-rep|unassign-center-rep', ['only'=> ['index']]);
        $this->middleware('permission:create-center', ['only'=> ['createShop']]);
        $this->middleware('permission:view-center', ['only'=> ['showShop']]);
        $this->middleware('permission:update-center', ['only'=> ['updateShop']]);
        $this->middleware('permission:delete-center', ['only'=> ['destroyShop']]);
        $this->middleware('permission:assign-center-partner', ['only'=> ['assignShop']]);
        $this->middleware('permission:unassign-center-partner', ['only'=> ['unassignShop']]);
        $this->middleware('permission:assign-center-rep', ['only'=> ['assignShopToRep']]);
        $this->middleware('permission:unassign-center-rep', ['only'=> ['unassignShopToRep']]);
    }

    public function index(){
        $data['shops'] = Shop::all();
        $data['total'] = $data['shops']->count();
        return $this->sendResponse($data, 'All registered centers');
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
      //  dd($data);
        $shop = Shop::create($data);
        return $this->sendResponse($shop, 'Center created succesfully');
     //   return response()->json(['message'=>'Created successfully'],200);

    }

    // View a shop

    public function showShop($id){
        try {
            $shop = Shop::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        $data['info'] =  DB::table('shops')
                        ->where('shops.id', '=', $shop->id)
                        ->select('shops.id AS shopID', 'shops.location AS shopLocation', 'shops.name AS shopName', 'shops.address AS shopAddress', 'shops.shopCode AS Code',)
                        ->get();

        $data['assignedPartner'] = DB::table('users')
                        ->where('users.id', '=', $shop->user_id)
                        ->select('users.id','users.firstName', 'users.lastName', 'users.phoneNum', 'users.email', 'users.address')
                        ->get();

        $data['assignedRep'] = DB::table('reps')
                        ->where('reps.id', '=', $shop->rep_id)
                        ->select('reps.id','reps.firstName', 'reps.lastName', 'reps.phoneNum', 'reps.email', 'reps.address')
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
            'choice' => 'nullable',
        ],
        $messages=[

        ]);

        $data = $request->all();
      //  $data['shopCode'] = "SH-". substr(md5(uniqid(rand(), true)),0,5);
        //dd($data);
        $shop->update($data);
        return $this->sendResponse($shop, 'Center updated succesfully.');

    }

    public function destroyShop($id){
        try {
            $shop = Shop::findOrfail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        if($shop->status === 'ASSIGNED-TO-PARTNER' || $shop->status2 === 'ASSIGNED-TO-REP'){
            return $this->sendError(['result'=>$shop->name.' is currently assigned. Unassign before deleting.']);

        }else{
            // Delete shop
            $shop->delete();
            return $this->sendResponse($shop, 'Center deleted successfully');

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

        } catch (ModelNotFoundException $th) {
            return $this->sendError('Partner not found in our records.', $th->getMessage());

        }

        try {
            $shop = Shop::where('id', $shopId)->firstOrFail();
        } catch (ModelNotFoundException $th) {
           return $this->sendError('Center not found in our records.', $th->getMessage());
        }

        //Check if center is already assigned to another partner.
        if($shop->user_id === NULL){
            //Assign shop to partner
        $shop->user()->associate($partner);
        $shop->status = "ASSIGNED-TO-PARTNER";
        $shop->save();

        // Save Audit
        $admin =   auth('admin')->user();
        $info =$admin->firstName. ' '. $admin->lastName. ' assigned center: '. $shop->name.' to '. $partner->lastName. ' '. $partner->firstName;
        $activity = new Activity();
        $activity->description = $info;
        $activity->save();

        return $this->sendResponse('Center: '.$shop->name.' is now assigned to '. $partner->lastName.' '. $partner->firstName, 'successful');

        }else{

            return $this->sendError('Center is already assigned to another partner');
        }


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
        if($shop->status === 'ASSIGNED-TO-PARTNER'){
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

            //Save audit
            $admin =   auth('admin')->user();
            $info =$admin->firstName. ' '. $admin->lastName. ' unassigned center: '. $shop->name.' from '. $partner->lastName. ' '. $partner->firstName;
            $activity = new Activity();
            $activity->description = $info;
            $activity->save();
            return $this->sendResponse($shop->name.' Unassigned from '. $partner->lastName, 'Successful');

        }

        return $this->sendError('Not allowed. Center is not assigned to partner');

    }
        /**
     * Unassign a shop to a rep.
     *
     * @return \Illuminate\Http\JsonResponse
     */

       public function assignShopToRep(Request $request){

        $repId = $request->repId;
        $shopId = $request->shopId;

        try {
           $rep = Rep::where('id', $repId)->firstOrFail();
           $shop = Shop::where('id', $shopId)->firstOrFail();
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred.', $th->getMessage());

        }

        $shopRep = Shop::where('rep_id', $repId)->first();

        if($shopRep === NULL){
            //check if center is already assigned to a rep
        if($shop->status2==='ASSIGNED-TO-REP'){
            return $this->sendError('Error: Center is already assigned.');

        }

        $shop->rep()->associate($rep)->save();
        $shop->status2 = "ASSIGNED-TO-REP";
        $shop->save();

        // Activities Table;
        $admin =   auth('admin')->user();
        $info =$admin->firstName. ' '. $admin->lastName. ' assigned center: '. $shop->name.' to '. $rep->lastName. ' '. $rep->firstName;
        $activity = new Activity();
        $activity->description = $info;
        $activity->save();

        return $this->sendResponse('Center:'.$shop->name.' is now assigned to '. $rep->lastName.' '. $rep->firstName, 'successful');


        }else{
            return $this->sendError('Not allowed. Center already has a rep assigned to it.');
        }

    }

    /**
     * Unassign a shop from a rep.
     *
     * @return \Illuminate\Http\JsonResponse
     */

     public function unassignShopToRep(Request $request){
        $shopId = $request->id;

        try {
            $shop = Shop::findOrFail($shopId);

        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());

        }
        // Check shop status
        if($shop->status2 === 'ASSIGNED-TO-REP'){
            $repId = $shop->rep_id;

            try {
                $rep = Rep::findorFail($repId);
            } catch (ModelNotFoundException $th) {
                return $this->sendError('An error occurred', $th->getMessage());

            }
            // Unassign shop.
            $shop->rep()->dissociate($rep);
            $shop->status2 = "UNASSIGNED";
            $shop->save();

            // Activities table
            $admin =   auth('admin')->user();
            $info = $admin->firstName. ' '. $admin->lastName.' unassigned shop: '. $shop->name. ' from '. $rep->lastName.' '. $rep->firstName;
            $activity = new Activity();
            $activity->description = $info;
            $activity->save();

            return $this->sendResponse($shop->name.' Unassigned from '. $rep->firstName, 'Successful');

        }

        return $this->sendError('Not allowed. Center is not assigned to rep');

    }

}
