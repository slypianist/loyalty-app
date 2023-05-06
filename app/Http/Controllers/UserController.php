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
use Illuminate\Database\QueryException;

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
        $data['partner'] = User::all();
        if ($data['partner']->count() == NULL) {
            return $this->sendResponse($data, 'No records');
        }
        $data['total'] = $data['partner']->count();
        return $this->sendResponse($data, 'Successful');

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

       try {
        $user =  User::create($data);
       } catch (QueryException $th) {
        return $this->sendError('Error: Duplicate entry detected');
       }

        return $this->sendResponse($user, 'Partner created successfully');

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
        $data['assignedShops'] = Shop::where('user_id', $partner->id)
                                 ->select('shopCode', 'name', 'address', 'location', 'status')
                                ->get();

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

        return $this->sendResponse($partner, ' Record update successfully');

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

        $partner->delete();
        return $this->sendResponse($partner, 'Record successfully deleted.');

    }




}
