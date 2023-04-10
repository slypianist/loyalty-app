<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
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
        $rule = Setting::all()->get(5);
        return response()->json(['rule'=>$rule]);

    }

    public function addLoyaltyRule(Request $request){
        $rule = new Setting();
        $rule->loyaltyRule = $request->value;
        $rule->save();
        return response()->json(['message'=>'Loyalty rule is created']);

    }

    public function getLoyaltyRule(){
        $rule = Setting::all()->first();
        return response()->json(['rule'=>$rule->loyaltyRule]);
    }

    public function updateLoyaltyRule(Request $request,$id){
        $rule = Setting::findOrFail($id);
        $rule->loyaltyRule = $request->loyaltyRule;
        $rule->update();

        return response()->json(['message'=>'Loyalty is update']);

    }
}
