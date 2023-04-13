<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\LoyaltyRule;
use Illuminate\Http\Request;
use App\Models\LoyaltyAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

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
        User::create($data);
        return response()->json(['message'=>'Partner created successfully.']);
    }

    public function showPartner(User $user){
        // Use DB Query BUilder
        DB::table('partner');
    }

    public function addLoyaltyPoints(Request $request){
        $id = $request->id;
        $invoice_id = $request->invoice;

        //Check if invoice is already used.
        $invoice = Invoice::where('id', $invoice_id)->first();

        //Check if customer is enrolled.
        $customer = Customer::findorFail($id)->first();

        if($customer){
            if($invoice->count()>= 1){
                return response()->json(['message'=>'This invoice has been used'],500);
            }
            $checkAcc = LoyaltyAccount::where('user_id', $id)->first();
            $rule = LoyaltyRule::where('status', 'ACTIVE')->first();

            // Update loyalty points if customer is already registered.
            if($checkAcc->count() == 1){
                $points = $request->point*($rule/100);
                $checkAcc->user_id = $id;
                $checkAcc->point = $checkAcc->point+=$points;
                $checkAcc->update();
                //Send Email to group and SMS to customer.

                return response()->json(['message'=>'Accrued Points have been updated']);


            }
            // Create a loyalty account if customer has none.
            elseif ($checkAcc->count() == 0) {
                $data = $request->all();
                $data['point'] = $request->point*($rule/100);
                $customer->loyaltyaccount()->create($data);
                // Send Email to group and SMS to customer.
                return response()->json(['message'=>'Loyalty account created & Points added'],200);

            }
            else{
                return response()->json(['error'=>'An error Occured adding points'],500);

            }

        }
        return response()->json(['message'=>'No record for this customer'],404);




    }

    public function makeClaims(Request $request){


    }

    public function getCustomerAccruedPoint(Request $request, $id){
        $id = $request->id;
        $point = LoyaltyAccount::where('user_id', $id)->first();
        return \response()->json(['message'=>$point],200);

    }


}
