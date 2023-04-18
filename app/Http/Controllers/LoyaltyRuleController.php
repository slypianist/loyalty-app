<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\LoyaltyRule;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LoyaltyRuleController extends BaseController
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
        $rule = new LoyaltyRule();
        $rule->loyaltyRule = $request->loyaltyRule;
        $rule->save();
        return $this->sendResponse($rule, 'Loyalty rule is created');
    }

    public function getLoyaltyRule(){
        $rule = Setting::all()->first();
        return $this->sendResponse($rule,'Loyalty Rule');

    }

    public function updateLoyaltyRule(Request $request,$id){
        try {

            $rule = Setting::findOrFail($id);

        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occured', $th->getMessage());
        }
        $rule->loyaltyRule = $request->loyaltyRule;
            $rule->update();

        return $this->sendResponse($rule, 'Update successful.');

    }


}
