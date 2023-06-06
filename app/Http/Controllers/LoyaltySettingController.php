<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Activity;
use App\Models\LoyaltyRule;
use Illuminate\Http\Request;
use App\Models\LoyaltySetting;
use App\Http\Controllers\BaseController;
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

    public function index(){
        $rule = LoyaltySetting::orderBy('id', 'DESC')->get();
        return $this->sendResponse($rule, 'successful.');

    }

    public function addLoyaltyRule(Request $request){

        $data = new LoyaltySetting();
        $data->name = $request->name;
        $data->rule = $request->rule;
        $data->save();

         // Activities Table;
         $admin =   auth('admin')->user();
         $info =$admin->firstName. ' '. $admin->lastName. ' created loyalty rule.';
         $activity = new Activity();
         $activity->description = $info;
         $activity->save();

        return $this->sendResponse($data, 'Loyalty rule is created');
    }

    public function getLoyaltyRule(){

        try {
            $rule = LoyaltySetting::where('status', 'ACTIVE')->get();
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error fetching rule.', $th->getMessage());
        }

        // Activities Table;
        $admin =   auth('admin')->user();
        $info =$admin->firstName. ' '. $admin->lastName. ' created loyalty rule';
        $activity = new Activity();
        $activity->description = $info;
        $activity->save();

        return $this->sendResponse($rule,'Loyalty Rule');

    }

    public function updateLoyaltyRule(Request $request,$id){

        $loyalty = LoyaltySetting::where('status', 'ACTIVE')->count();

        if($loyalty >= 1){
            LoyaltySetting::query()->update(['status'=>'INACTIVE']);
            try {

                $data = LoyaltySetting::findOrFail($id);

            } catch (ModelNotFoundException $th) {
                return $this->sendError('An error occured', $th->getMessage());
            }

                $input = $request->all();

                $data->update($input);

                // Activities Table;
                $admin =   auth('admin')->user();
                $info =$admin->firstName. ' '. $admin->lastName. ' updated loyalty rule';
                $activity = new Activity();
                $activity->description = $info;
                $activity->save();

            return $this->sendResponse($data, 'Loyalty rule updated successful.');
           // return $this->sendError('Not allowed... You can only have a single active rule.');
        }

        try {

            $data = LoyaltySetting::findOrFail($id);

        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occured', $th->getMessage());
        }

            $input = $request->all();

            $data->update($input);

             // Activities Table;
             $admin =   auth('admin')->user();
             $info =$admin->firstName. ' '. $admin->lastName. ' updated loyalty app';
             $activity = new Activity();
             $activity->description = $info;
             $activity->save();

        return $this->sendResponse($data, 'Loyalty rule updated successful.');

    }

    public function destroyLoyaltyRule($id){
        try {
            $data = LoyaltySetting::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred.');
        }

        $data->delete();

         // Activities Table;
         $admin =   auth('admin')->user();
         $info =$admin->firstName. ' '. $admin->lastName. ' deleted loyalty rule';
         $activity = new Activity();
         $activity->description = $info;
         $activity->save();

        return $this->sendResponse($data, 'Rule deleted successfully.');
    }


}
