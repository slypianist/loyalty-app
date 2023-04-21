<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\LoyaltyRule;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\LoyaltySetting;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LoyaltySettingController extends BaseController
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

   /*  public function index(){
        $rule = Setting::all()->get(5);
        return response()->json(['rule'=>$rule]);

    } */

    public function addLoyaltyRule(Request $request){

        $data = new LoyaltySetting();
        $data->name = $request->name;
        $data->rule = $request->rule;
        $data->save();
        return $this->sendResponse($data, 'Loyalty rule is created');
    }

    public function getLoyaltyRule(){

        try {
            $rule = LoyaltySetting::firstOrFail();
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error fetching rule.', $th->getMessage());
        }

        return $this->sendResponse($rule,'Loyalty Rule');

    }

    public function updateLoyaltyRule(Request $request,$id){
        try {

            $data = LoyaltySetting::findOrFail($id);

        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occured', $th->getMessage());
        }
            $data->name = $request->name;
            $data->rule = $request->rule;
            $data->update();

        return $this->sendResponse($data, 'Loyalty rule updated successful.');

    }


}
