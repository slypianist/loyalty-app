<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Withdrawal;
use App\Models\LoyaltyRule;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function index(){
        $partner = User::orderBy('id', 'ASC')->paginate(5);
        if ($partner->count() == NULL) {
            return $this->sendResponse($partner, 'No records');
        }
        return $this->sendResponse($partner, 'Successful');

    }

    public function createPartner(Request $request){
        $this->validate($request,[
            'firstName' => 'required',
            'lastName'=> 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'phoneNum' => 'required',
            'address' => 'nullable',
        ]);

        $data = $request->all();

        if($request->has('password')){

            $data['password'] = Hash::make($request->password);

        }else{

            $data = $request->except('password');
        }

        $data = $request->all();
      $user =  User::create($data);
        return $this->sendResponse($user, 'Partner created successfully');
       // return response()->json(['message'=>'Partner created successfully.']);
    }



    // Show Partners details.

    public function showPartner($id){
        try {
            $partner = User::findOrFail($id);

        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        $data['partner'] =  DB::table('users')
        ->where('users.id', $id)
       ->select('users.firstName AS firstName', 'users.lastName AS lastName', 'users.address AS address',
             'users.phoneNum AS phoneNumber', 'users.email AS email')
        ->get();
        $data['assignedShops'] = Shop::where('user_id', $partner->id)->get();

        return $this->sendResponse($data, 'Successful');

    }
        // Update a Partner Info
    public function updatePartner(Request $request, $id){

        $this->validate($request,[
            'firstName' => 'required',
            'lastName'=> 'required',
            'email' => 'required|email',
            'phoneNum' => 'required',
            'address' => 'nullable',
        ]);
        $data = $request->all();
        try {
            $partner = User::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        $partner->update($data);

        return $this->sendResponse($partner, 'Update successful');

    }

    public function deletePartner($id){
        try {
            $partner = User::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        if($partner->shops()->count()>= 1){
            return $this->sendError('You cannot delete a partner that is currently assigned to a shop(s).');
        }

        return $this->sendResponse($partner, 'Record successfully deleted.');

    }


}
